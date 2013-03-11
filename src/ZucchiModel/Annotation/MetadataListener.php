<?php
namespace ZucchiModel\Annotation;

use Zend\EventManager\Event;

use Zend\EventManager\EventManagerInterface;
/**
 * Created by JetBrains PhpStorm.
 * User: matt
 * Date: 11/03/13
 * Time: 13:44
 * To change this template use File | Settings | File Templates.
 */
class MetadataListener
{

    /**
     * Attach listeners
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners = array(
            $events->attach('configureModel', array($this, 'configureModelRelationships')),
            $events->attach('configureModelFields', array($this, 'configureModelFields')),
        );
    }

    /**
     * remove listeners from events
     * @param EventManagerInterface $events
     */
    public function detach(EventManagerInterface $events)
    {
        array_walk($this->listeners, array($events,'detach'));
        $this->listeners = array();
    }

    public function configureModelRelationships(Event $event)
    {
        $metadata = $event->getTarget();
        $relationships = array();
        $annotations = $event->getParam('annotation');
        foreach ($annotations as $annotation) {
            if ($annotation instanceof \ZucchiModel\Annotation\Relationship) {
                $rel = $annotation->getRelationship();
                $relationships[$rel['name']] = $rel;
            }
        }

        $metadata['relationships'] = $relationships;

    }

    public function configureModelFields(Event $event)
    {
        $metadata = $event->getTarget();
        $property = $event->getParam('property');
        $annotations = $event->getParam('annotation');
        foreach ($annotations as $annotation) {
            if ($annotation instanceof \ZucchiModel\Annotation\Field) {
                $metadata[$property] = $annotation->getField();
            }


        }
    }
}
