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
 * Metadata Listener
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage Annotation
 */
class MetadataListener
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
     */
    public function prepareModelMetadata(Event $event)
    {
        $model = $event->getParam('model');
        $relationships = $event->getParam('relationships');
        $target = array();
        $annotations = $event->getTarget();

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
     */
    public function prepareFieldMetadata(Event $event)
    {
        $metadata = $event->getTarget();
        $property = $event->getParam('property');
        $annotations = $event->getParam('annotation');
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\Field) {
                $metadata[$property] = $annotation->getField();
            }
        }
    }
}
