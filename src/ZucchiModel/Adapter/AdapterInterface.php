<?php
/**
 * AdapterInterface.php - (http://zucchi.co.uk) 
 *
 * @link      http://github.com/zucchi/{PROJECT_NAME} for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\Adapter;

use ZucchiModel\Query\Criteria;
use ZucchiModel\Persistence\Container;
use ZucchiModel\Metadata\MetaDataContainer;

/**
 * AdapterInterface
 *
 * Description of class
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModel\Adapter
 * @subpackage
 * @category
 */
interface AdapterInterface
{
    /**
     * Retrieve metadata for class
     *
     * @param $class
     * @return mixed
     */
    public function getMetaData($class);

    /**
     * Build and return query object from criteria
     *
     * @param Criteria $criteria
     * @param MetaDataContainer $metadata
     * @return mixed
     */
    public function buildQuery(Criteria $criteria, MetaDataContainer $metadata);

    /**
     * Execute supplied query and return result
     *
     * @param $query
     * @return mixed
     */
    public function execute($query);

    /**
     * Method to write models from container to database
     * @param Container $container
     * @return mixed
     */
    public function write(Container $container);
}
