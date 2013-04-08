<?php
/**
 * MetaDataContainer.php - (http://zucchi.co.uk) 
 *
 * @link      http://github.com/zucchi/{PROJECT_NAME} for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\Metadata;

use ZucchiModel\Metadata\Adapter;

/**
 * MetaDataContainer
 *
 * Description of class
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage Metadata
 * @category 
 */
class MetaDataContainer
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Fields
     */
    protected $fields;

    /**
     * @var Relationships
     */
    protected $relationships;

    /**
     * @var Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * @param Adapter\AdapterInterface $adapter
     * @return $this
     */
    public function setAdapter(Adapter\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param Fields $fields
     * @return $this
     */
    public function setFields(Fields $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return Fields
     */
    public function getFields()
    {
        return $this->fields;

    }

    /**
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Relationships $relationships
     * @return $this
     */
    public function setRelationships(Relationships $relationships)
    {
        $this->relationships = $relationships;
        return $this;
    }

    /**
     * @return Relationships
     */
    public function getRelationships()
    {
        return $this->relationships;
    }


}