<?php
/**
 * ZendDb.php - (http://zucchi.co.uk) 
 *
 * @link      http://github.com/zucchi/{PROJECT_NAME} for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\Metadata\Adapter;

use Zend\Db\Sql\Where;
use ZucchiModel\Query\Criteria;

/**
 * ZendDb
 *
 * Description of class
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage Metadata
 * @category
 */
class ZendDb extends AbstractAdapter
{
    /**
     * Cache of columns found for this model
     *
     * @var array
     */
    protected $columnMap = array();

    /**
     * Cache of constraints found for this model
     *
     * @var array
     */
    protected $constraints = array();

    /**
     * Cache of target hierarchy found for this model
     *
     * @var array
     */
    protected $hierarchy = array();

    /**
     * List of all the targets
     *
     * @var array
     */
    protected $targets = array();

    /**
     * Add to criteria to find a relationship
     *
     * @param $model
     * @param Criteria $criteria
     * @param array $relationship
     * @return Criteria
     */
    public function addRelationship($model, Criteria $criteria, array $relationship)
    {
        // Create where clause with actually value, pointed at by
        // mappedKey, while we have access to the model.
        $where = new Where();

        if (property_exists($model, 'getProperty')) {
            $getProperty = $model->getProperty;
        }

        switch ($relationship['type']) {
            case 'toOne':
            case 'toMany':
                $where->equalTo($relationship['mappedBy'], $getProperty($relationship['mappedKey']));
                $criteria->setWhere($where);
                break;
            case 'ManytoMany':
                $relationship['mappedKey'] = $getProperty($relationship['mappedKey']);
                $criteria->setJoin(array($relationship));
                break;
        }

        return $criteria;
    }

    /**
     * Return all columns. False if none
     * are set.
     *
     * @return array|bool
     */
    public function getColumnMap()
    {
        if (!empty($this->columnMap)) {
            return $this->columnMap;
        }

        return false;
    }

    /**
     * Return all constraints or a selection by type.
     * False if none are set.
     *
     * @param string|null $type
     * @return array|bool
     */
    public function getConstraints($type = null)
    {
        if (!empty($this->constraints)) {
            if (!$type) {
                return $this->constraints;
            } else {
                return $this->constraints[$type];
            }
        }

        return false;
    }

    /**
     * Return hierarchy. False if not
     * are set.
     *
     * @return array|bool
     */
    public function getHierarchy()
    {
        if (!empty($this->hierarchy)) {
            return $this->hierarchy;
        }

        return false;
    }
    
    /**
     * Return list of all the targets.
     * False if not are set.
     *
     * @return array|bool
     */
    public function getTargets()
    {
        if (!empty($this->targets)) {
            return $this->targets;
        }

        return false;
    }

    /**
     * Get full Table Hierarchy from given target
     *
     * @param $currentTable
     * @param $foreignKeys
     * @return array
     */
    protected function getTargetHierarchy($currentTable, $foreignKeys)
    {
        $hierarchy = array();
        foreach ($foreignKeys as $foreignKeyTable => $foreignKey) {
            if ($foreignKey['tableName'] == $currentTable) {
                $hierarchy[$currentTable][] = $foreignKeyTable;
                $hierarchy[$foreignKeyTable] = array();
                $hierarchy = array_merge($hierarchy, $this->getTargetHierarchy($foreignKeyTable, $foreignKeys));
            }
        }

        return $hierarchy;
    }
}