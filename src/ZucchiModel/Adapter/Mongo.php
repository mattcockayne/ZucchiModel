<?php
/**
 * Mongo.php - (http://zucchi.co.uk) 
 *
 * @link      http://github.com/zucchi/{PROJECT_NAME} for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\Adapter;

use ZucchiModel\Metadata\MetaDataContainer;
use ZucchiModel\Model;
use ZucchiModel\Query\Criteria;

/**
 * Mongo
 *
 * Description of class
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel\Adapter
 * @subpackage
 * @category
 */
class Mongo extends AbstractAdapter
{
    /**
     * Retrieve metadata for given targets.
     *
     * @param array $targets
     * @return mixed
     */
    public function getMetaData(Array $targets)
    {

    }

    /**
     * Build and return query object from criteria.
     *
     * @param Criteria $criteria
     * @param MetaDataContainer $metadata
     * @return mixed|void
     */
    public function buildQuery(Criteria $criteria, MetaDataContainer $metadata)
    {

    }

    /**
     * Build and return query object from criteria.
     *
     * @param Criteria $criteria
     * @param MetaDataContainer $metadata
     * @return mixed|void
     */
    public function buildCountQuery(Criteria $criteria, MetaDataContainer $metadata)
    {

    }

    /**
     * Find and return hydrated result set.
     *
     * @param Criteria $criteria
     * @param MetaDataContainer $metadata
     * @return mixed|void
     */
    public function find(Criteria $criteria, MetaDataContainer $metadata)
    {

    }

    /**
     * Execute supplied query and return result.
     *
     * @param $query
     * @return mixed
     */
    public function execute($query)
    {

    }

    /**
     * @param Model\Container $container
     * @return mixed|void
     */
    public function write(Model\Container $container)
    {

    }
}