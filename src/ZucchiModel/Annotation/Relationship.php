<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZucchiModel\Annotation;

use Zend\Form\Annotation\AbstractArrayAnnotation;

/**
 * Relationship annotation
 *
 * @Annotation
 */
class Relationship extends AbstractArrayAnnotation
{
    protected $validKeys = array(
        'name','model','type','mappedKey','mappedBy',
    );

    public function __construct(array $data)
    {
        parent::__construct($data);

        foreach (array_keys($this->value) as $key) {
            if (!in_array($key, $this->validKeys)) {
                throw new \RuntimeException('Invalid definition of "' . $key . '" in Relationship Annotation');
            }
        }
    }

    /**
     * Retrieve the class type
     *
     * @return null|string
     */
    public function getRelationship()
    {
        return $this->value;
    }
}
