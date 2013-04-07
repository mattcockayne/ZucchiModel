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
use Zend\Db\Sql\Where;

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
     * @param int $paginatedPageSize
     * @return bool|ResultSet\HydratingResultSet|ResultSet\PaginatedResultSet
     * @throws \RuntimeException
     */
    public function getRelationship($model, $nameOfRelationship, $paginatedPageSize = 0)
    {
        if (!($classMetadata = $this->getMetadata(get_class($model)))) {
            throw new \RuntimeException(sprintf('No Metadata found for %s.', var_export($model, true)));
        }

        if (!($relationshipMetadata = $classMetadata['relationships'][$nameOfRelationship])) {
            throw new \RuntimeException(sprintf('No Relationship found for %s', $nameOfRelationship));
        }

        switch ($relationshipMetadata['type']) {
            case 'toOne':
                // Create where clause with actually value, pointed at by
                // mappedKey, while we have access to the model.
                $where = new Where();
                $where->equalTo($relationshipMetadata['mappedBy'], $this->getModelProperty($model, $relationshipMetadata['mappedKey']));

                // Create Criteria for query
                $criteria = new Criteria(array(
                    'model' => $relationshipMetadata['model'],
                    'where' => $where
                ));

                // Find relationship
                return $this->findOne($criteria);
                break;
            case 'toMany':
                // Create where clause with actually value, pointed at by
                // mappedKey, while we have access to the model.
                $where = new Where();
                $where->equalTo($relationshipMetadata['mappedBy'], $this->getModelProperty($model, $relationshipMetadata['mappedKey']));

                // Create Criteria for query
                $criteria = new Criteria(array(
                    'model' => $relationshipMetadata['model'],
                    'where' => $where
                ));

                // Find relationship
                return $this->findAll($criteria, $paginatedPageSize);
                break;
            case 'ManytoMany':
                // Replace mappedKey with actually value, while we have access to the
                // model.
                $relationshipMetadata['mappedKey'] = $model->$relationshipMetadata['mappedKey'];

                // Create Criteria for query
                $criteria = new Criteria(array(
                    'model' => $relationshipMetadata['model'],
                    'join' => array($relationshipMetadata),
                ));

                // Find relationship
                return $this->findAll($criteria, $paginatedPageSize);
                break;
            default:
                throw new \RuntimeException(sprintf('Invalid Relationship Type. Given %s', var_export($relationshipMetadata)));
        }
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
        if (!isset($metadata['metadata']) || empty($metadata['metadata'])) {
            throw new \RuntimeException(sprintf('No Target Metadata can be found for this Model. %s given.', var_export($model, true)));
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
        if (!isset($metadata['metadata']) || empty($metadata['metadata'])) {
            throw new \RuntimeException(sprintf('No Target Metadata can be found for this Model. %s given.', var_export($model, true)));
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
     * Get a model property, checks for
     * unmapped properties.
     *
     * @param $model
     * @param $property
     * @return mixed the property value
     * @throws \RuntimeException if it can not find the property
     */
    protected function getModelProperty($model, $property)
    {
        if (is_object($model)) {
            if (property_exists($model, $property)) {
                return $model->$property;
            } else {
                if (property_exists($model, 'unmappedProperties') && !empty($model->unmappedProperties[$property])) {
                    return $model->unmappedProperties[$property];
                }
            }
        }

        // Can not find the property, throw error. Note false and null can not be returned instead as they can be
        // valid values for properties.
        throw new \RuntimeException(sprintf('Property of %s not found on %s.', $property, var_export($model, true)));
    }

    /**
     * Persist Model to the database.
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
        $this->persistenceContainer = null;
        $this->persistenceContainer = new Persistence\Container();
    }
}
