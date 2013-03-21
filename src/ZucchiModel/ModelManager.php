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
use ZucchiModel\Hydrator;
use ZucchiModel\Annotation\MetadataListener;
use ZucchiModel\Metadata;
use ZucchiModel\Query\Criteria;

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
                $dataSourceMetadata = array();
                // populate datasource details
                foreach ($model['dataSource'] as $dataSource) {
                    $dataSourceMetadata[$dataSource] = $dbMeta->getTable($dataSource);
                }

                // Check we have matched the given dataSource to a Table Name
                if (!empty($dataSourceMetadata)) {
                    $this->modelMetadata[$class]['dataSourceMetadata'] = $dataSourceMetadata;
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

    // TODO: test compound keys
    // TODO: take into account schema and table names in foreignKeys
    // TODO: store results in mapCache
    // TODO: break out sql into its own driver(matt may change this) so we can add NoSql etc.
    // TODO: add function with factory to new sql driver
    public function findOne(Criteria $criteria)
    {
        $select = $this->sql->select();

        // Get model and check it exists
        $model = $criteria->getModel();
        if (!class_exists($model)) {
            throw new \RuntimeException(sprintf('Model does not exist. %s given.', var_export($model, true)));
        }

        // Get metadata for the given model
        $metadata = $this->getMetadata($model);

        // Check dataSource and metadata exist
        if (!isset($metadata['dataSourceMetadata']) || empty($metadata['dataSourceMetadata'])) {
            throw new \RuntimeException(sprintf('No Data Source Metadata can be found for this Model. %s given.', var_export($model, true)));
        }

        // List of Data Source Names
        $dataSources = array();

        // Fields to select
        $selectColumns = array();

        // Create a look up for all the foreign keys
        $foreignKeys = array();

        foreach ($metadata['dataSourceMetadata'] as $dataSource => $metadata) {
            // Create list of Data Sources
            $dataSources[] = $dataSource;

            // Build up an array of all the Columns to select
            $columns = $metadata->getColumns();
            array_walk(
                $columns,
                function ($column) use (&$selectColumns, $dataSource) {
                    if (!isset($selectColumns[$column->getName()])) {
                        $selectColumns[$column->getName()] = $dataSource;
                    }
                }
            );

            // Build up an array of all the Foreign Key Relationships
            $constraints = $metadata->getConstraints();
            array_walk(
                $constraints,
                function ($constraint) use (&$foreignKeys) {
                    $foreignKeys[$constraint->getReferencedTableName()] = array(
                        'tableName' => $constraint->getTableName(),
                        'columns' => $constraint->getColumns(),
                        'referencedColumns' => $constraint->getReferencedColumns()
                    );
                }
            );
        }

        // Get the first Data Source which will be the From Table
        $from = array_shift($dataSources);

        // Create lookup to match Table name to alias
        $tableNameLookup = array($from => 0);

        // Set form Table and Columns if present
        $select->from(array('t0' => $from));
        $columns = array_keys($selectColumns, $from);
        if (!empty($columns)) {
            $select->columns($columns);
        }

        $joins = array();

        // Building Join references
        foreach ($dataSources as $referencedTableName) {
            // Make sure required Metadata is present
            if (empty($foreignKeys[$referencedTableName]['tableName']) ||
                empty($foreignKeys[$referencedTableName]['columns']) ||
                empty($foreignKeys[$referencedTableName]['referencedColumns']) ||
                (count($foreignKeys[$referencedTableName]['columns']) != count($foreignKeys[$referencedTableName]['referencedColumns']))
            ) {
                throw new \RuntimeException(sprintf('Invalid Foreign Key Metadata defined for %s.', $referencedTableName));
            }

            $tableName = $foreignKeys[$referencedTableName]['tableName'];
            $columns = $foreignKeys[$referencedTableName]['columns'];
            $referencedColumns = $foreignKeys[$referencedTableName]['referencedColumns'];

            // If not used before add table to temporary lookup
            if (!isset($tableNameLookup[$tableName])) {
                $tableNameLookup[$tableName] = count($tableNameLookup);
            }
            $tableFrom = $tableNameLookup[$tableName];

            // If referenced table has not used before add table to temporary lookup
            if (!isset($tableNameLookup[$referencedTableName])) {
                $tableNameLookup[$referencedTableName] = count($tableNameLookup);
            }
            $tableTo = $tableNameLookup[$referencedTableName];

            // Create array of map on for join
            $on = array();
            for ($i = 0; $i < count($columns); $i++) {
                $on[] = 't' . $tableFrom . '.' . $columns[$i] . ' = t' . $tableTo . '.' . $referencedColumns[$i];
            }

            // Find all columns to "select" for this join
            $columns = array_keys($selectColumns, $referencedTableName);

            // Setting join reference
            $joins[$tableTo] = array(
                'table' => array('t' . $tableTo => $referencedTableName),
                'on' => implode(' AND ', $on),
                'columns' => (!empty($columns)) ? $columns : array()
            );
        }

        // Check if we have any joins
        if (!empty($joins)) {
            // Sort into table join order e.g. t0, t1, t2
            ksort($joins);

            // Add joins for other Data Sources
            foreach($joins as $join) {
                $select->join($join['table'], $join['on'], $join['columns'], 'left');
            }
        }

        // Check and apply any "where"
        if ($where = $criteria->getWhere()) {
            $select->where($where);
        }

        // Check and apply any "offset"
        if ($offset = $criteria->getOffset()) {
            $select->offset($offset);
        }

        // Force "limit" to one
        $select->limit(1);

        // Build and run the DB statement
        $statememt = $this->sql->prepareStatementForSqlObject($select);
        $results = $statememt->execute();

        // Check for single result
        if ($result = $results->current()) {
            // Create new model
            $model = new $model();

            // Hydrate single result.
            $hydrator = new Hydrator\ObjectProperty();
            $hydrator->hydrate($result, $model);

            // Return result
            return $model;
        }

        // Return false if nothing found
        return false;
    }

}
