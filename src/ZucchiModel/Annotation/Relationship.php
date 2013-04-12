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
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage Annotation
 */
class Relationship extends AbstractArrayAnnotation
{
    const TO_ONE = 'toOne';

    const TO_MANY = 'toMany';

    const MANY_TO_MANY = 'ManytoMany';

    protected $validKeys = array(
        'name','model','type','mappedKey','mappedBy','foreignKey','foreignBy','referencedBy','referencedOrder'
    );

    protected $validTypes = array(
        self::TO_ONE, self::TO_MANY, self::MANY_TO_MANY
    );

    protected $requiredKeys = array(
        self::TO_ONE => array(
            'name','model','type','mappedKey','mappedBy'
        ),
        self::TO_MANY => array(
            'name','model','type','mappedKey','mappedBy'
        ),
        self::MANY_TO_MANY => array(
            'name','model','type','mappedKey','mappedBy','foreignKey','foreignBy','referencedBy'
        )
    );

    public function __construct(Array $data)
    {
        parent::__construct($data);

        $foundKeys = array_keys($this->value);

        if (!isset($this->value['type']) || !($type = $this->value['type'])) {
            throw new \RuntimeException(sprintf('Required type missing from Relationship annotation. Given "%s".', (implode(',',$foundKeys))));
        }

        if (!in_array($type, $this->validTypes)) {
            throw new \RuntimeException(sprintf('Invalid type of relationship "%s" defined in Relationship annotation.', $this->value['type']));
        }

        $missingKeys = array_diff($this->requiredKeys[$type], $foundKeys);

        if (!empty($missingKeys)) {
            throw new \RuntimeException(sprintf('Required data for "%s" missing from Relationship annotation.', (implode(',',$missingKeys))));
        }

        foreach ($foundKeys as $key) {
            if (!in_array($key, $this->validKeys)) {
                throw new \RuntimeException(sprintf('Invalid definition of "%s" in Relationship annotation.', $key));
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
