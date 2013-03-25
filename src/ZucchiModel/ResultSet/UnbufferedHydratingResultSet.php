<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\ResultSet;

use Iterator;
use ArrayObject;
use Zend\Stdlib\Hydrator\ArraySerializable;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Unbuffered Hydrating ResultSet
 *
 * Use this class when a result set might exhaust
 * all available memory. If result set is small, use
 * Buffered Hydrating Result Set for increased performance.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage ResultSet
 * @category
 */
class UnbufferedHydratingResultSet extends AbstractResultSet
{
    /**
     * Always returns false as count can not be performed
     * on Unbuffered Result Sets.
     *
     * @return bool
     */
    public function count()
    {
        trigger_error('Count is not available with an Unbuffered Result Set', E_USER_NOTICE);

        return false;
    }
}