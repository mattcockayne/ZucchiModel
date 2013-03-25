<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModel;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;

use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Annotation\Parser;
use Zend\Code\Reflection\ClassReflection;

use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;


use ZucchiModel\Adapter\AdapterInterface;
use ZucchiModel\Hydrator;
use ZucchiModel\Annotation\MetadataListener;
use ZucchiModel\Metadata;
use ZucchiModel\Query\Criteria;

use ZucchiModel\ResultSet\UnbufferedHydratingResultSet;


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
     * @var \ZucchiModel\Adapter\AdapterInterface
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
        'ZucchiModel\Annotation\Target',
    );

    /**
     * Construct ModelManager with supplied ZucchiModel Adapter
     *
     * @param \ZucchiModel\Adapter\AdapterInterfacee $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->setAdapter($adapter);
    }

    /**
     * Get ZucchiModel Adapter
     *
     * @return \ZucchiModel\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set Zend Db Adapter
     *
     * @param \ZucchiModel\Adapter\AdapterInterface $adapter
     * @return ModelManager
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
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
            $relationships = new Metadata\Relationships();
            $fields = new Metadata\Fields();

            // Find all the Model Metadata
            if ($annotations = $reflection->getAnnotations($am)) {
                $event = new Event();
                $event->setName('prepareModelMetadata');
                $event->setTarget($annotations);
                $event->setParam('model', $model);
                $event->setParam('relationships', $relationships);
                $em->trigger($event);
            }

            // Cache Model Metadata
            $this->modelMetadata[$class]['model'] = $model;

            // Cache Relationships Metadata
            $this->modelMetadata[$class]['relationships'] = $relationships;

            // Find all the Fields Metadata
            if ($properties = $reflection->getProperties()) {
                $event = new Event();
                $event->setName('prepareFieldMetadata');
                $event->setTarget($fields);
                foreach ($properties as $property) {
                    if ($annotation = $property->getAnnotations($am)) {
                        $event->setParam('property', $property->getName());
                        $event->setParam('annotation', $annotation);
                        $em->trigger($event);
                    }
                }
            }

            // Cache Fields Metadata
            $this->modelMetadata[$class]['fields'] = $fields;

            // Check for Data Sources and get their Table Name
            if (isset($model['target']) && !empty($model['target'])){
                $this->modelMetadata[$class]['metadata'] = $this->getAdapter()->getMetaData($model['target']);
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
     * Find and return a single model
     *
     * @param Criteria $criteria
     * @return bool
     * @throws \RuntimeException
     * @todo: test compound keys
     * @todo: take into account schema and table names in foreignKeys
     * @todo: store results in mapCache
     * @todo: break out sql into its own driver(matt may change this) so we can add NoSql etc.
     * @todo: add function with factory to new sql driver
     */
    public function findOne(Criteria $criteria)
    {
        // Get model and check it exists
        $model = $criteria->getModel();
        if (!class_exists($model)) {
            throw new \RuntimeException(sprintf('Model does not exist. %s given.', var_export($model, true)));
        }

        // Get metadata for the given model
        $metadata = $this->getMetadata($model);

        // Check dataSource and metadata exist
        if (!isset($metadata['metadata']) || empty($metadata['metadata'])) {
            throw new \RuntimeException(sprintf('No Adapter Specific Metadata can be found for this Model. %s given.', var_export($model, true)));
        }

        $criteria->setLimit(1);

        $query = $this->getAdapter()->buildQuery($criteria, $metadata);

        $results = $this->getAdapter()->execute($query);

        // Check for single result
        if (!$result = $results->current()) {
            // return false as no result found
            return false;
        }

        // Create new model
        $model = new $model();

        // Hydrate single result.
        $hydrator = new Hydrator\ObjectProperty();
        $hydrator->hydrate($result, $model);

        // Return result
        return $model;
    }

    /**
     * find and return a collection of models
     *
     * @param Criteria $criteria
     * @param bool $bufferResult
     * @return bool|HydratingResultSet|UnbufferedHydratingResultSet
     * @throws \RuntimeException
     */
    public function findAll(Criteria $criteria, $bufferResult = true)
    {
        // Get model and check it exists
        $model = $criteria->getModel();
        if (!class_exists($model)) {
            throw new \RuntimeException(sprintf('Model does not exist. %s given.', var_export($model, true)));
        }

        // Get metadata for the given model
        $metadata = $this->getMetadata($model);

        // Check dataSource and metadata exist
        if (!isset($metadata['metadata']) || empty($metadata['metadata'])) {
            throw new \RuntimeException(sprintf('No Data Source Metadata can be found for this Model. %s given.', var_export($model, true)));
        }

        $query = $this->getAdapter()->buildQuery($criteria, $metadata);

        $results = $this->getAdapter()->execute($query);

        if (!$results instanceof \Iterator) {
            // if not an iterator then return false
            return false;
        }

        if ($bufferResult) {

            if (method_exists($results, 'buffer')) {
                $results->buffer();
            }

            $hydratingResultSet = $this->getAdapter()->getHydratingResultSet(new Hydrator\ObjectProperty, new $model);
        } else {
            $hydratingResultSet = new UnbufferedHydratingResultSet(new Hydrator\ObjectProperty, new $model);
        }

        $hydratingResultSet->initialize($results);

        return $hydratingResultSet;
    }
}
