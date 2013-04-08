<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModel;

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
use ZucchiModel\Persistence;
use ZucchiModel\Query\Criteria;
use ZucchiModel\ResultSet;

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

    protected $persistenceContainer;

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
     * @param AdapterInterface $adapter
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
        $adapter->setEventManager($this->getEventManager());
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

        $hydrationListener = new Hydrator\HydrationListener($this);
        $hydrationListener->attach($events);

        $behaviourListener = new Behaviour\BehaviourListener($this);
        $behaviourListener->attach($events);

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
            $this->modelMetadata[$class] = $md = new Metadata\MetaDataContainer();

            // Get the Model's Annotations
            $reflection  = new ClassReflection($class);
            $am = $this->getAnnotationManager();
            $em = $this->getEventManager();

            // Find all the Model Metadata
            if ($annotations = $reflection->getAnnotations($am)) {
                $event = new Event();
                $event->setName('prepareModelMetadata');
                $event->setTarget($annotations);
                $event->setParam('model', $md->getModel());
                $event->setParam('relationships', $md->getRelationships());
                $em->trigger($event);
            }

            // Find all the Fields Metadata
            if ($properties = $reflection->getProperties()) {
                $event = new Event();
                $event->setName('prepareFieldMetadata');
                $event->setTarget($md->getFields());
                foreach ($properties as $property) {
                    if ($annotation = $property->getAnnotations($am)) {
                        $event->setParam('property', $property->getName());
                        $event->setParam('annotation', $annotation);
                        $em->trigger($event);
                    }
                }
            }

            // Check for Data Sources and get their Table Name
            if ($target = $md->getModel()->getTarget()) {
                $md->setAdapter($this->getAdapter()->getMetaData($target));
            }
        }

        return $this->modelMetadata[$class];
    }

    /**
     * Get Relationships
     *
     * @param $model
     * @param $relationship
     * @param int $paginatedPageSize
     * @return bool|ResultSet\HydratingResultSet|ResultSet\PaginatedResultSet
     */
    public function getRelationship($model, $relationship, $paginatedPageSize = 0)
    {
        $metadata = $this->getMetadata(get_class($model));
        $relationship = $metadata->getRelationships()->getRelationship($relationship);

        $criteria = new Criteria(array(
            'model' => $relationship['model'],
        ));
        $criteria = $metadata->getAdapter()->addRelationship(
            $model,
            $criteria,
            $relationship
        );

        switch ($relationship['type']) {
            case 'toOne':
                return $this->findOne($criteria);
                break;
            case 'toMany':
            case 'ManytoMany':
                return $this->findAll($criteria, $paginatedPageSize);
                break;
        }

        return false;
    }

    /**
     * Find and return a single model
     *
     * @param Criteria $criteria
     * @return bool
     * @throws \RuntimeException
     * @todo: take into account schema and table names in foreignKeys
     * @todo: store results in mapCache
     * @todo: add listener for converting JSON, currency etc.
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
        if (!$metadata->getAdapter()) {
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

        $model = new $model();

        // Trigger Hydration events
        $event = new Event('preHydrate', $model, array('data' => $result));
        $this->getEventManager()->trigger($event);

        $event->setName('hydrate');
        $this->getEventManager()->trigger($event);

        $event->setName('postHydrate');
        $this->getEventManager()->trigger($event);
        
        // Return result
        return $model;
    }

    /**
     * Find and return a collection of models
     *
     * @param Criteria $criteria
     * @param int $paginatedPageSize if greater than 1 then paginate results using this as Page Size
     * @return bool|ResultSet\HydratingResultSet|ResultSet\PaginatedResultSet
     * @throws \RuntimeException
     */
    public function findAll(Criteria $criteria, $paginatedPageSize = 0)
    {
        // Get model and check it exists
        $model = $criteria->getModel();

        if (!class_exists($model)) {
            throw new \RuntimeException(sprintf('Model does not exist. %s given.', var_export($model, true)));
        }

        // Get metadata for the given model
        $metadata = $this->getMetadata($model);

        // Check dataSource and metadata exist
        if (!$metadata->getAdapter()) {
            throw new \RuntimeException(sprintf('No Adapter Specific Metadata can be found for this Model. %s given.', var_export($model, true)));
        }

        // Check if a Paginated Result Set is wanted,
        // else return standard Hydrating Result Set
        if ($paginatedPageSize > 0) {
            $resultSet = new ResultSet\PaginatedResultSet($this, $criteria, $paginatedPageSize);
        } else {
            $query = $this->getAdapter()->buildQuery($criteria, $metadata);

            $results = $this->getAdapter()->execute($query);

            if (!$results instanceof \Iterator) {
                // if not an iterator then return false
                return false;
            }

            $resultSet = new ResultSet\HydratingResultSet($this->getEventManager(), new $model);
            if (method_exists($results, 'buffer')) {
                $results->buffer();
            }
            $resultSet->initialize($results);
        }

        return $resultSet;
    }

    /**
     * Return row count
     *
     * @param Criteria $criteria
     * @return int|bool
     * @throws \RuntimeException
     */
    public function countAll(Criteria $criteria)
    {
        // Get model and check it exists
        $model = $criteria->getModel();

        if (!class_exists($model)) {
            throw new \RuntimeException(sprintf('Model does not exist. %s given.', var_export($model, true)));
        }

        // Get metadata for the given model
        $metadata = $this->getMetadata($model);

        // Check dataSource and metadata exist
        if (!$metadata->getAdapter()) {
            throw new \RuntimeException(sprintf('No Adapter Specific Metadata can be found for this Model. %s given.', var_export($model, true)));
        }

        // Force limit and offset to null
        $criteria->setLimit(null);
        $criteria->setOffset(null);

        $query = $this->getAdapter()->buildCountQuery($criteria, $metadata);

        $result = $this->getAdapter()->execute($query);

        if ($count = $result->current()) {
            return $count['count'];
        } else {
            return false;
        }
    }

    /**
     * Add model to persistenceContainer for later writing to datasource
     *
     * @param $model
     */
    public function persist($model)
    {
        if (!$this->persistenceContainer) {
            $this->persistenceContainer = new Persistence\Container();
        }

        // if container doe not already have a reference to the model
        if (!$this->persistenceContainer->contains($model)) {

            // Get metadata for the given model
            $metadata = $this->getMetadata(get_class($model));

            // Trigger Persistence events for behaviours
            $event = new Event('prePersist', $this->persistenceContainer, array(
                'model' => $model,
                'metadata' => $metadata,
            ));
            $this->getEventManager()->trigger($event);

            $this->persistenceContainer->attach($model, $metadata);

            $event->setName('postPersist');
            $this->getEventManager()->trigger($event);

        }
    }

    /**
     * Write persisted models to dataSource
     */
    public function write()
    {
        if ($this->persistenceContainer instanceof Persistence\Container) {
            $this->getAdapter()->write($this->persistenceContainer);
        }

        // clean out Persistence Container
        unset($this->persistenceContainer);
        $this->persistenceContainer = null;
    }
}
