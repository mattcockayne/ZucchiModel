<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\Hydrator;

use \Zend\Stdlib\Hydrator\ObjectProperty as PropertyHydrator;
use Zend\Stdlib\Exception;

/**
 * Hydrator that only works with existing Object properties
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage Hydrator
 * @category
 */
class ObjectProperty extends PropertyHydrator
{
    /**
     * Hydrate an object by populating public properties
     *
     * Hydrates an object by setting public properties of the object.
     *
     * @param  array $data
     * @param  object $object
     * @return object
     * @throws Exception\BadMethodCallException for a non-object $object
     */
    public function hydrate(array $data, $object)
    {
        if (!is_object($object)) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s expects the provided $object to be a PHP object)', __METHOD__
            ));
        }

        $populated = array();
        $unmappedProperties = array();
        foreach ($data as $property => $value) {
            // Check property to stop misc data being mapped to the Model.
            // Instead this data is stored in a key value pair array called
            // unmappedProperties.
            if (property_exists($object, $property) && !in_array($property, $populated)) {
                $object->$property = $this->hydrateValue($property, $value);
                $populated[] = $property;
            } else {
                $unmappedProperties[$property] = $value;
            }
        }

        // Set unmappedProperties on the model
        $object->unmappedProperties = $unmappedProperties;

        return $object;
    }
}