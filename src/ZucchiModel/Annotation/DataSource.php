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
 * Table annotation
 *
 * Use this annotation to specify the database table to use
 *
 * @Annotation
 */
class DataSource extends AbstractArrayOrStringAnnotation
{
    /**
     * Retrieve the class type
     *
     * @return null|string
     */
    public function getDataSource()
    {
        return $this->value;
    }
}
