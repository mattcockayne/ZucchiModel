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
    public $constraints = array();

    /**
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
     * @param $target
     * @param null $type
     * @return array
     */
    public function getConstraints($target, $type = null)
    {
        if (!isset($this->constraints[$target])) {

            $tableMetadata = $this->offsetGet($target);

            $primary = array();
            $foreign = array();
            $unique = array();

            // Build up an array of all the Foreign Key Relationships
            $constraints = $tableMetadata->getConstraints();
            array_walk(
                $constraints,
                function ($constraint) use (&$foreign, &$primary, &$unique) {
                    switch ($constraint->getType()) {
                        case 'FOREIGN KEY':
                            $foreign[$constraint->getReferencedTableName()] = array(
                                'tableName' => $constraint->getTableName(),
                                'columnReferenceMap' => array_combine($constraint->getReferencedColumns(), $constraint->getColumns()),
                            );
                            break;
                        case 'PRIMARY KEY':
                            if (!isset($primary[$constraint->getTableName()])) {
                                $primary[$constraint->getTableName()] = array_fill_keys($constraint->getColumns(), null);
                            } else {
                                $primary[$constraint->getTableName()] = array_merge($primary[$constraint->getTableName()], array_fill_keys($constraint->getColumns(), null));
                            }
                            break;
                        case 'UNIQUE':
                            break;
                        default:
                            break;
                    }
                }
            );
            $this->constraints[$target] = array(
                'primary' => $primary,
                'foreign' => $foreign,
                'unique' => $unique,
            );
        }

        if ($type) {
            return $this->constraints[$target][$type];
        }
        return $this->constraints;
    }
}