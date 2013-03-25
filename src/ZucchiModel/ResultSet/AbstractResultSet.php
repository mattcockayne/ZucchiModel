<?php
/**
 * AbstractResultSet.php - (http://zucchi.co.uk) 
 *
 * @link      http://github.com/zucchi/{PROJECT_NAME} for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\ResultSet;

use Zucchi\Event\EventProviderTrait;

/**
 * AbstractResultSet
 *
 * Description of class
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage ResultSet
 * @category 
 */
abstract class AbstractResultSet
{
    use EventProviderTrait;

    public function __construct()
    {

    }

}