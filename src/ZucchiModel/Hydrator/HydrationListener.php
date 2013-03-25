<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModel\Hydrator;

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
class HydrationListener
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
            $events->attach('preHydrate', array($this, 'preHydrate')),
            $events->attach('hydrate', array($this, 'hydrate')),
            $events->attach('postHydrate', array($this, 'postHydrate')),
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

    public function preHydrate(Event $event)
    {

    }

    public function hydrate(Event $event)
    {
        $target = $event->getTarget();
        $data = $event->getParam('data');

        $hydrator = new ObjectProperty();
        $hydrator->hydrate($data, $target);
    }

    public function postHydrate(Event $event)
    {

    }

}
