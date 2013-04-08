<?php
/**
 * ZendDb.php - (http://zucchi.co.uk) 
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\Adapter;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Metadata\Metadata;
use Zend\EventManager\EventManager;
use ZucchiModel\Query\Criteria;
use ZucchiModel\Persistence;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

/**
 * ZendDb
 *
 * Description of class
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage Adapter
 * @category
 */
class ZendDb extends AbstractAdapter
{
    /**
     * Sql object for creating queries
     *
     * @var Sql $sql
     */
    protected $sql;

    /**
     * Create ZendDb adapter with supplied dataSource
     *
     * @param Adapter $dataSource
     */
    public function __construct(Adapter $dataSource)
    {
        $this->setDataSource($dataSource);
    }

    /**
     * Set the datasource
     *
     * @param Adapter $dataSource
     * @return $this
     */
    public function setDataSource($dataSource)
    {
        $this->sql = new Sql($dataSource);

        return parent::setDataSource($dataSource);
    }

    /**
     * Retrieve metadata for class
     *
     * @param array $tables
     * @return array|mixed
     * @throws \RuntimeException
     */
    public function getMetaData($tables = array())
    {
        $dbMeta = new Metadata($this->getDataSource());
        $metadata = array();
        // populate datasource details
        foreach ($tables as $table) {
            $metadata[$table] = $dbMeta->getTable($table);
        }

        // Check we have matched the given dataSource to a Table Name
        if (empty($metadata)) {
            throw new \RuntimeException(sprintf('Data Source mapping not found for %s.', var_export($tables, true)));
        }

        return $metadata;
    }

    /**
     * Build and return query object from criteria
     *
     * @param Criteria $criteria
     * @param array $metadata
     * @return mixed|\Zend\Db\Sql\Select
     */
    public function buildQuery(Criteria $criteria, Array $metadata)
    {
        // List of Data Source Names
        $dataSources = array();

        // Fields to select
        $selectColumns = array();

        // Create a look up for all the foreign keys
        $foreignKeys = array();

        foreach ($metadata['metadata'] as $dataSource => $metadata) {
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
                    if ('FOREIGN KEY' == $constraint->getType()) {
                        $foreignKeys[$constraint->getReferencedTableName()] = array(
                            'tableName' => $constraint->getTableName(),
                            'columns' => $constraint->getColumns(),
                            'referencedColumns' => $constraint->getReferencedColumns()
                        );
                    }
                }
            );
        }

        // Get the first Data Source which will be the From Table
        $from = array_shift($dataSources);

        // Create Select Query object
        $select = $this->sql->select();

        // Set form Table and Columns if present
        $select->from(array('t0' => $from));
        $columns = array_keys($selectColumns, $from);
        if (!empty($columns)) {
            $select->columns($columns);
        }

        $dataSources = array_keys($this->getTargetHierarchy($from, $foreignKeys));
        array_shift($dataSources);

        // Get array of any joins
        $joins = $this->determineJoins($dataSources, $from, $selectColumns, $foreignKeys);

        // Add any additional joins to join array
        $joins = array_merge($joins, $this->determineAdditionalJoins($criteria));

        // Check if we have any joins
        if (!empty($joins)) {
            // Add joins for other Data Sources
            foreach($joins as $join) {
                $select->join($join['table'], $join['on'], $join['columns'], 'left');
            }
        }

        // Check and apply any "where"
        if ($where = $criteria->getWhere()) {
            $select->where($where);
        }

        // Check and apply any "limit"
        if ($limit = $criteria->getLimit()) {
            $select->limit($limit);

            // Check and apply any "offset"
            if ($offset = $criteria->getOffset()) {
                $select->offset($offset);
            }
        }

        // Check and apply any "order"
        if ($orderBy = $criteria->getOrderBy()) {
            $select->order($orderBy);
        }

        return $select;
    }

    /**
     * Build and return a count query object from criteria
     *
     * @param Criteria $criteria
     * @param array $metadata
     * @return \Zend\Db\Sql\Select
     */
    public function buildCountQuery(Criteria $criteria, Array $metadata)
    {
        // Create normal Select Query object
        $select = $this->buildQuery($criteria, $metadata);

        // Replace column select with Count(*)
        $select->reset('columns')->columns(array('count' => new Expression('COUNT(*)')));

        return $select;
    }

    /**
     * Execute supplied query and return result
     * 
     * @param $query
     * @return mixed
     */
    public function execute($query)
    {
        // Build and run the DB statement
        $statememt = $this->sql->prepareStatementForSqlObject($query);
        $results = $statememt->execute();

        return $results;
    }

    /**
     * Determines the required joins for a query
     *
     * @param $dataSources
     * @param $from
     * @param $selectColumns
     * @param $foreignKeys
     * @return array
     * @throws \RuntimeException
     * @todo: add select column lookup on where
     */
    protected function determineJoins($dataSources, $from, $selectColumns, $foreignKeys)
    {
        // Create lookup to match Table name to alias
        $tableNameLookup = array($from => 0);

        // Create empty return array
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

        // Sort into table join order e.g. t0, t1, t2
        ksort($joins);

        return $joins;
    }

    /**
     * Workout joins for supplied additional data
     *
     * @param Criteria $criteria
     * @param array $joins
     * @return array
     */
    public function determineAdditionalJoins(Criteria $criteria, $joins = array())
    {
        if ($additionalJoins = $criteria->getJoin()) {
            $where = $criteria->getWhere() ?: new Where();
            $order = $criteria->getOrderBy() ?: array();

            foreach ($additionalJoins as $additionalJoin) {
                $on = sprintf('%s.' . $additionalJoin['foreignBy'] . ' = %s.' . $additionalJoin['foreignKey'], $additionalJoin['referencedBy'], 't0');
                $joins[] = array(
                    'table' => $additionalJoin['referencedBy'],
                    'on' => $on,
                    'columns' => array()
                );

                $where->equalTo($additionalJoin['referencedBy'] . '.' . $additionalJoin['mappedBy'], $additionalJoin['mappedKey']);
                if (!empty($additionalJoin['referencedOrder'])) {
                    $order[] = $additionalJoin['referencedOrder'];
                }
            }

            $criteria->setWhere($where);
            $criteria->setOrderBy($order);
        }

        return $joins;
    }

    /**
     * Get full Target Hierarchy
     * 
     * @param $current
     * @param $foreignKeys
     * @return array
     */
    public function getTargetHierarchy($current, $foreignKeys) {
        $h = array();
        foreach ($foreignKeys as $fkTable => $fk) {
            if ($fk['tableName'] == $current) {
                $h[$current][] = $fkTable;
                $h[$fkTable] = array();
                $h = array_merge($h, $this->getTargetHierarchy($fkTable, $foreignKeys));
            }
        }
        return $h;
    }

    /**
     * Persist given model.
     *
     * @param $model
     * @param array $metadata
     * @throws \RuntimeException
     */
    public function persist($model, Array $metadata)
    {
        if (in_array('ZucchiModel\Behaviour\ChangeTrackingTrait', class_uses($model))) {
            if (!$model->isChanged()) {
                return false;
            }
        }

        // List of Data Source Names
        $dataSources = array();

        // Fields to select
        $selectColumns = array();

        // Create a look up for all the foreign keys
        $foreignKeys = array();

        $primaryKeys = array();

        foreach ($metadata['metadata'] as $dataSource => $metadata) {
            // Create list of Data Sources
            $dataSources[] = $dataSource;

            // Build up an array of all the columns and the table to write to
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
                function ($constraint) use (&$foreignKeys, &$primaryKeys) {
                    switch ($constraint->getType()) {
                        case 'FOREIGN KEY':
                            $foreignKeys[$constraint->getReferencedTableName()] = array(
                                'tableName' => $constraint->getTableName(),
                                'columnReferenceMap' => array_combine($constraint->getReferencedColumns(), $constraint->getColumns()),
                            );
                            break;
                        case 'PRIMARY KEY':
                            if (!isset($primaryKeys[$constraint->getTableName()])) {
                                $primaryKeys[$constraint->getTableName()] = array_fill_keys($constraint->getColumns(), null);
                            } else {
                                $primaryKeys[$constraint->getTableName()] = array_merge($primaryKeys[$constraint->getTableName()], array_fill_keys($constraint->getColumns(), null));
                            }
                            break;
                        default:
                            break;
                    }
                }
            );
        }

        $current = reset($dataSources);
        $dataSources = $this->getTargetHierarchy($current, $foreignKeys);
        $dataSources = array_reverse($dataSources, true);

        foreach ($dataSources as $dataSource => $related) {
            $columns = array_keys($selectColumns, $dataSource);
            $updateColumns = array();
            if (isset($primaryKeys[$dataSource])) {
                foreach ($primaryKeys[$dataSource] as $primaryKey => $value) {
                    if (isset($foreignKeys[$dataSource]['columnReferenceMap'][$primaryKey])) {
                        try {
                            $primaryKeys[$dataSource][$primaryKey] = $value = $this->getModelProperty($model, $foreignKeys[$dataSource]['columnReferenceMap'][$primaryKey]);
                        } catch (\RuntimeException $e) {
                            $this->setModelProperty($model, $foreignKeys[$dataSource]['columnReferenceMap'][$primaryKey], null);
                            $primaryKeys[$dataSource][$primaryKey] = $value = null;
                        }
                    } else {
                        try {
                            $primaryKeys[$dataSource][$primaryKey] = $value = $this->getModelProperty($model, $primaryKey);
                        } catch (\RuntimeException $e) {
                            $this->setModelProperty($model, $primaryKey, null);
                            $primaryKeys[$dataSource][$primaryKey] = $value = null;
                        }
                    }
                    $updateColumns[$primaryKey] = $value;
                }
            } else {
                throw new \RuntimeException(sprintf('No primary keys found for %s.', $dataSource));
            }

            foreach ($columns as $column) {
                if (!in_array($column, $primaryKeys[$dataSource]) && $column != 'createdAt' && $column != 'updatedAt') {
                    if (isset($foreignKeys[$dataSource]['columnReferenceMap'][$column])) {
                        $updateColumns[$column] = $this->getModelProperty($model, $foreignKeys[$dataSource]['columnReferenceMap'][$column]);
                    } else {
                        $updateColumns[$column] = $this->getModelProperty($model, $column);
                    }
                }
            }

            if (array_search(null, $primaryKeys[$dataSource])) {
                // insert
                $this->insert($dataSource, $updateColumns, $foreignKeys, $primaryKeys, $model);
            } else {
                if (in_array('ZucchiModel\Behaviour\ChangeTrackingTrait', class_uses($model))) {
                    $changes = $model->getChanges();
                    if (array_keys($changes, array_keys($primaryKeys[$dataSource]))) {
                        //insert
                        $this->insert($dataSource, $updateColumns, $foreignKeys, $primaryKeys, $model);
                        //delete?
                    } else {
                        //update
                        $this->update($dataSource, $updateColumns, $primaryKeys);
                    }
                } else {
                    //select
                    $select = $this->sql->select($dataSource);
                    $select->columns(array('count' => new Expression('COUNT(*)')));
                    $select->where($primaryKeys[$dataSource]);
                    $result = $this->execute($select);
                    if ($count = $result->current()) {
                        if ($count['count'] > 0) {
                            //update
                            $this->update($dataSource, $updateColumns, $primaryKeys);
                        }
                    }
                    //insert
                    $this->insert($dataSource, $updateColumns, $foreignKeys, $primaryKeys, $model);
                }
            }
        }
    }

    /**
     * Insert given model
     *
     * @param $dataSource
     * @param $insertColumns
     * @param $foreignKeys
     * @param $primaryKeys
     * @param $model
     */
    private function insert($dataSource, $insertColumns, $foreignKeys, &$primaryKeys, &$model)
    {
        $query = $this->sql->insert($dataSource);
        $query->values($insertColumns);
        $result = $this->execute($query);
        if ($ids = $result->getGeneratedValue()) {
            if (is_array($ids)) {
                $primaryKeys[$dataSource] = $ids;
                foreach ($ids as $key => $value) {
                    // set model values.
                    $this->setModelProperty($model, $key, $value);
                }
            } else {
                foreach ($primaryKeys[$dataSource] as $key=>$value) {
                    $primaryKeys[$dataSource][$key] = $ids;
                    if (isset($foreignKeys[$dataSource]['columnReferenceMap'][$key])) {
                        $this->setModelProperty($model, $foreignKeys[$dataSource]['columnReferenceMap'][$key], $ids);
                    }
                }
            }
        }
    }

    /**
     * Update given model.
     *
     * @param $dataSource
     * @param $updateColumns
     * @param $primaryKeys
     */
    private function update($dataSource, $updateColumns, $primaryKeys)
    {
        $query = $this->sql->update($dataSource);
        $query->set($updateColumns);
        $query->where($primaryKeys[$dataSource]);
        $result = $this->execute($query);
    }

    /**
     * Get Model Property
     *
     * @param $model
     * @param $property
     * @return mixed
     * @throws \RuntimeException
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
     * Set Model Property
     *
     * @param $model
     * @param $property
     * @param $value
     * @throws \RuntimeException
     */
    protected function setModelProperty($model, $property, $value)
    {
        if (is_object($model)) {
            if (property_exists($model, $property)) {
                $model->$property = $value;
            } else {
                $model->unmappedProperties[$property] = $value;
            }
        } else {
            throw new \RuntimeException(sprintf('Model is not an object. Given %s.', var_export($model, true)));
        }
    }

    public function write(Persistence\Container $container)
    {
        $em = $this->getEventManager();

        $container->rewind();
        while($container->valid()) {
            $model = $container->current();
            $metadata = $container->getInfo();

            // Trigger Write events for validation and manipulation
            $event = new Event('preWrite', $model, array(
                'metadata' => $metadata,
                'adapter' => $this,
            ));
            $preWriteResult = $this->getEventManager()->trigger($event);


            // if stopped assume failed validation of some sort
            if ($preWriteResult->stopped()) {
                // ??? throw something
            }

            // @todo: write model to db


            $event->setName('postWrite');
            $this->getEventManager()->trigger($event);

            $container->detach($model);
            $container->next();
        }


    }
}
