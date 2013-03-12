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
 * @package ZucchiModel
 * @subpackage Annotation
 */
class Relationship extends AbstractArrayAnnotation
{
    protected $validKeys = array(
        'name','model','type','mappedKey','mappedBy',
    );

    protected $validTypes = array(
        'toOne','toMany',
    );

    protected $requiredKeys = array(
        'name','model','type','mappedKey','mappedBy',
    );

    public function __construct(array $data)
    {
        parent::__construct($data);

        $foundKeys = array_keys($this->value);

        $missingKeys = array_diff($this->requiredKeys, $foundKeys);

        if (!empty($missingKeys)) {
            throw new \RuntimeException(sprintf('Required data for "%s" missing from  Relationship annotation', (implode(',',$missingKeys))));
        }

        foreach ($foundKeys as $key) {
            if (!in_array($key, $this->validKeys)) {
                throw new \RuntimeException(sprintf('Invalid definition of "%s" in Relationship annotation', $key));
            }
        }

        if (!in_array($this->value['type'], $this->validTypes)) {
            throw new \RuntimeException(sprintf('Invalid type of relationship  ("%s") defined in Relationship annotation', $this->value['type']));
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
