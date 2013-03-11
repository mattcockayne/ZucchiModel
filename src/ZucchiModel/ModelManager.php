<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiDatabase;

use Zend\Db\Adapter\AdapterInterface;

/**
 * Object Manager for
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage ModelManager
 * @category
 */
class ModelManager
{
    /**
     * @var \Zend\Db\Adapter\AdapterInterface
     */
    protected $adapter;


    public function __construct(AdapterInterface $adapter)
    {
        $this->setAdapter($adapter);
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * helper to find objects by criteria
     * @param $criteria
     * @return ObjectManager
     */
    public function find($criteria)
    {
        return $this;
    }
}
