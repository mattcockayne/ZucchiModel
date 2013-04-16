<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModel\Annotation;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;

use ZucchiModel\Annotation;

/**
 * Annotation Listener
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage Annotation
 */
class AnnotationListener
{
    /**
     * List of attached events.
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * Attach listeners.
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners = array(
            $events->attach('prepareModelMetadata', array($this, 'prepareModelMetadata')),
            $events->attach('prepareFieldMetadata', array($this, 'prepareFieldMetadata')),
        );
    }

    /**
     * Remove listeners from events.
     *
     * @param EventManagerInterface $events
     */
    public function detach(EventManagerInterface $events)
    {
        array_walk($this->listeners, array($events, 'detach'));
        $this->listeners = array();
    }

    /**
     * Event to build Model Metadata for given model.
     *
     * @param Event $event
     * @throws \UnexpectedValueException
     */
    public function prepareModelMetadata(Event $event)
    {
        $target = array();
        $model = $event->getParam('model');

        // Check model is the correct type
        if (!($model instanceof \Traversable)) {
            throw new \UnexpectedValueException(sprintf('Model must be Traversable. Given %s.', var_export($model, true)));
        }

        $relationships = $event->getParam('relationships');

        // Check relationships is the correct type
        if (!($relationships instanceof \Traversable)) {
            throw new \UnexpectedValueException(sprintf('Relationship must be Traversable. Given %s.', var_export($relationships, true)));
        }

        $annotations = $event->getTarget();

        // Annotations must be traversable
        if (!($annotations instanceof \Traversable)) {
            throw new \UnexpectedValueException(sprintf('Annotations must be Traversable. Given %s.', var_export($annotations, true)));
        }

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\Relationship) {
                $rel = $annotation->getRelationship();
                $relationships[$rel['name']] = $rel;
            }

            if ($annotation instanceof Annotation\Target) {
                $target = $annotation->getTarget();
            }
        }

        $model['target'] = $target;
    }

    /**
     * Event to build Field Metadata for given model.
     *
     * @param Event $event
     * @throws \UnexpectedValueException
     */
    public function prepareFieldMetadata(Event $event)
    {
        $metadata = $event->getTarget();

        // Check model is the correct type
        if (!($metadata instanceof \Traversable)) {
            throw new \UnexpectedValueException(sprintf('Metadata must be Traversable. Given %s.', var_export($metadata, true)));
        }

        $property = $event->getParam('property');

        // Check model is the correct type
        if (!is_string($property) || $property == '') {
            throw new \UnexpectedValueException(sprintf('A string of Property is expected. Given %s.', var_export($property, true)));
        }

        $annotations = $event->getParam('annotation');

        // Annotations must be traversable
        if (!($annotations instanceof \Traversable)) {
            throw new \UnexpectedValueException(sprintf('Annotations must be Traversable. Given %s.', var_export($annotations, true)));
        }

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\Field) {
                $metadata[$property] = $annotation->getField();
            }
        }
    }
}
