<?php
/**
 * AbstractAdapter.php - (http://zucchi.co.uk) 
 *
 * @link      http://github.com/zucchi/{PROJECT_NAME} for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\Adapter;

use Zucchi\Event\EventProviderTrait;

/**
 * AbstractAdapter
 *
 * Description of class
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModel\Adapter
 * @subpackage
 * @category
 */
abstract class AbstractAdapter implements AdapterInterface
{
    use EventProviderTrait;

    /**
     * The Adapter/interface for the db i.e. Zend\Db\AdapterAbstract | MongoClient
     *
     * @var mixed
     */
    protected $dataSource = null;

    /**
     * Set the datasource
     *
     * @param $dataSource
     * @return $this
     */
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    /**
     * Get the datasource
     *
     * @return mixed|null
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

}