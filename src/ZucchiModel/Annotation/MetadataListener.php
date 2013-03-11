<?php
namespace ZucchiModel\Annotation;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;

use ZucchiModel\Annotation;
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
            $events->attach('prepareModelMetadata', array($this, 'prepareModelMetadata')),
            $events->attach('prepareFieldMetadata', array($this, 'prepareFieldMetadata')),
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

    public function prepareModelMetadata(Event $event)
    {
        $metadata = $event->getTarget();
        $relationships = array();
        $dataSource = '';
        $annotations = $event->getParam('annotations');

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\Relationship) {
                $rel = $annotation->getRelationship();
                $relationships[$rel['name']] = $rel;
            }

            if ($annotation instanceof Annotation\DataSource) {
                $dataSource = $annotation->getDataSource();
            }
        }

        $metadata['relationships'] = $relationships;
        $metadata['dataSource'] = $dataSource;


    }

    public function prepareFieldMetadata(Event $event)
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
