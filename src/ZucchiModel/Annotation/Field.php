<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZucchiModel\Annotation;

use Zend\Form\Annotation\AbstractArrayOrStringAnnotation;

/**
 * Field annotation
 *
 * @Annotation
 */
class Field extends AbstractArrayOrStringAnnotation
{
    /**
     * Allowed types for fields
     *
     * @var array
     */
    private $allowedTypes = array(
        'string',
        'boolean',
        'float',
        'date',
        'time',
        'datetime',
        'json'
    );

    /**
     * Constructor
     *
     * @param array $data
     * @throws \RuntimeException if no valid type is given in annotation
     */
    public function __construct(array $data) {
        parent::__construct($data);
        $type = $this->getField();

        // Test the given type is a valid allowed type
        // If not throw
        if (!in_array($type, $this->allowedTypes)) {
            throw new \RuntimeException(sprintf('%s is not a valid Field definition"', var_export($type, true)));
        }
    }

    /**
     * Retrieve the annotation field value,
     * should be the type
     *
     * @return null|string
     */
    public function getField()
    {
        return $this->value;
    }
}
