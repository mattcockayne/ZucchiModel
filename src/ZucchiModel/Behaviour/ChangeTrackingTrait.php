<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModel\Behaviour;



/**
 * Change Tracking Behaviour Trait
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage Behaviour
 * @category
 */
class ChangeTrackingTrait
{
    /**
     * @var array
     */
    private $cleanData = array();

    /**
     * set the clean data to compare with
     * @param array $data
     *
     */
    public function setCleanData(array $data)
    {
        $this->cleanData = $data;
        return $this;
    }

    /**
     * Function to retrive the changed fields of an entity
     *
     * @param bool $original retrieve the original values
     * @return array of changed values
     */
    public function getChanges($original = false)
    {
        if ($original) {
            $a = $this->cleanData;
            $b = get_object_vars($this);
            unset($b['cleanData']);
        } else {
            $a = get_object_vars($this);
            $b = $this->cleanData;
            unset($a['cleanData']);
        }

        return array_udiff_assoc($a, $b, function($a, $b) {

            if (is_array($a) || is_array($b)) {
                return 0;
            }

            if ($a !== $b){
                return 1;
            }

            return 0;
        });
    }

    /**
     * Test if a specific field has changed
     *
     * @param string $field
     * @return boolean
     */
    public function isChanged($field = null)
    {
        $changes = $this->getChanges();
        if (null == $field && count($changes)) {
            return true;
        }

        if (isset($changes[$field])) {
            return true;
        }

        return false;
    }
}
