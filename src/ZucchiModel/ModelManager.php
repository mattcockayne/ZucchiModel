<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModel;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Annotation\Parser;
use Zend\Code\Reflection\ClassReflection;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use ZucchiModel\Annotation\MetadataListener;
use ZucchiModel\Metadata;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\AbstractSql;


/**
 * Model Manager for ORM
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage ModelManager
 * @category
 */
class ModelManager implements EventManagerAwareInterface
{
    /**
     * Zend Db Adapter used for connecting to the database.
     *
     * @var \Zend\Db\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * SQL object used to create SQL statements.
     *
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var AnnotationManager;
     */
    protected $annotationManager;

    /**
     * Mapping data for loaded models
     *
     * @var array
     */
    protected $modelMetadata = array();

    /**
     * Collection of know Annotations related to ModelManager
     *
     * @var array
     */
    protected $registeredAnnotations = array(
        'ZucchiModel\Annotation\Field',
        'ZucchiModel\Annotation\Relationship',
        'ZucchiModel\Annotation\DataSource',
    );

    /**
     * Construct ModelManager with supplied Zend Db Adapter
     *
     * @param \Zend\Db\Adapter\AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->setAdapter($adapter);
    }

    /**
     * Get Zend Db Adapter
     *
     * @return \Zend\Db\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set Zend Db Adapter
     *
     * @param \Zend\Db\Adapter\AdapterInterface $adapter
     * @return ModelManager
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->sql = new Sql($adapter);

        return $this;
    }

    /**
     * Get event manager
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }

    /**
     * Set event manager instance
     *
     * @param  EventManagerInterface $events
     * @return AnnotationBuilder
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_class($this),
        ));
        $metadataListener = new MetadataListener();
        $metadataListener->attach($events);
        $this->eventManager = $events;
        return $this;
    }

    /**
     * Get Annotation Manager
     *
     * @return \Zend\Code\Annotation\AnnotationManager
     */
    public function getAnnotationManager()
    {
        if (!$this->annotationManager) {
            $this->annotationManager = new AnnotationManager();
            $parser = new Parser\DoctrineAnnotationParser();
            foreach ($this->registeredAnnotations as $annotation) {
                $parser->registerAnnotation($annotation);
            }
            $this->annotationManager->attach($parser);
        }
        return $this->annotationManager;
    }

    /**
     * Set Annotation Manager
     *
     * @param \Zend\Code\Annotation\AnnotationManager $annotationManager
     */
    public function setAnnotationManager(AnnotationManager $annotationManager)
    {
        $this->annotationManager = $annotationManager;
    }

    /**
     * Get Metadata for a specified class
     *
     * @param string $class
     * @return mixed
     * @throws \RuntimeException
     */
    public function getMetadata($class)
    {
        if (!array_key_exists($class, $this->modelMetadata)) {
            // Add class to cache
            $this->modelMetadata[$class] = array();

            // Get the Model's Annotations
            $reflection  = new ClassReflection($class);
            $am = $this->getAnnotationManager();
            $em = $this->getEventManager();

            $model = new Metadata\Model();
            $fields = new Metadata\Fields();

            // Find all the Model Metadata
            if ($annotations = $reflection->getAnnotations($am)) {
                $event = new Event();
                $event->setName('prepareModelMetadata');
                $event->setTarget($model);
                $event->setParam('annotations', $annotations);
                $em->trigger($event);
            }

            // Cache Model Metadata
            $this->modelMetadata[$class]['model'] = $model;

            // Find all the Fields Metadata
            if ($properties = $reflection->getProperties()) {
                $event = new Event();
                $event->setName('prepareFieldMetadata');
                $event->setTarget($fields);
                foreach ($properties as $property) {
                    if ($annotation = $property->getAnnotations($am)) {
                        $event->setParam('property',$property->getName());
                        $event->setParam('annotation',$annotation);
                        $em->trigger($event);
                    }
                }
            }

            // Cache Fields Metadata
            $this->modelMetadata[$class]['fields'] = $fields;

            // Check for Data Sources and get their Table Name
            if (isset($model['dataSource']) && !empty($model['dataSource'])){
                $dbMeta = new \Zend\Db\Metadata\Metadata($this->getAdapter());
                $sourceMeta = array();
                // populate datasource details
                foreach ($model['dataSource'] as $dataSource) {
                    $sourceMeta[$dataSource] = $dbMeta->getTable($dataSource);
                }

                // Check we have matched the given dataSource to a Table Name
                if (!empty($sourceMeta)) {
                    $this->modelMetadata[$class]['dataSource'] = $sourceMeta;
                } else {
                    throw new \RuntimeException(sprintf('Data Source mapping not found for %s.', var_export($model['dataSource'], true)));
                }
            }
        }

        return $this->modelMetadata[$class];
    }

    /**
     * Get Relationships
     *
     * @param $model
     * @param $nameOfRelationship
     */
    public function getRelationship($model, $nameOfRelationship)
    {
        if (isset($this->modelMetadata[get_class($model)]['relationships'][$nameOfRelationship])) {
            // lookup and return relationship;

        }
    }

    /**
     * Release stored model
     *
     * @param $model
     * @param bool $releaseRelationships
     */
    public function release($model, $releaseRelationships = true)
    {

    }

    /**
     * create a new Query
     *
     * @todo: requires consideration of NoSQL queries
     * @return \Zend\Db\Sql\Sql
     */
    public function createQuery()
    {
        $query = new Sql($this->getAdapter());
        return $query;
    }

    /**
     * execute query and return mapped results
     * @param $criteria
     * @return ResultSet?
     */
    public function query(AbstractSql $query)
    {
        // detect if aggregation, group by or function present.
        // if present throw exception as not mapable results

        // detect tables being accessed

        // update query to return ALL columns

        // pass results to mapToModel method

        // if array of results needs to use custom ResultSet that
        // will use model manager for hydrating on iteration.

        // return primary entity or resultset from query


    }

    public function mapToModel($data, $modelOrModelNameToMapTo)
    {
        // map data to Model

        // cache model in memory?

        // return model

    }



}
