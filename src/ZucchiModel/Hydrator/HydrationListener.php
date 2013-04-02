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
use ZucchiModel\ModelManager;

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
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * Constructor
     *
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->setModelManager($modelManager);
    }

    /**
     * Set Model Manager
     *
     * @param ModelManager $modelManager
     * @return $this
     */
    public function setModelManager(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
        return $this;
    }

    /**
     * Get Model Manager
     *
     * @return ModelManager
     */
    public function getModelManager()
    {
        return $this->modelManager;
    }

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
            $events->attach('preHydrate.cast', array($this, 'castDateTime')),

            $events->attach('hydrate', array($this, 'hydrate')),

            $events->attach('postHydrate', array($this, 'postHydrate')),
        );
    }

    /**
     * Remove listeners from events
     *
     * @param EventManagerInterface $events
     */
    public function detach(EventManagerInterface $events)
    {
        array_walk($this->listeners, array($events,'detach'));
        $this->listeners = array();
    }

    /**
     * Handles casting data values of know types
     * to objects etc. E.g 'datetime' is cast to
     * new \DateTime()
     *
     * @param Event $event
     */
    public function preHydrate(Event $event)
    {
        $metadata = $this->getModelManager()->getMetadata(get_class($event->getTarget()));
        $data = $event->getParam('data');

        // If metadata is given, cast values by type
        if ($fields = $metadata['fields']) {
            foreach ($data as $key => $value) {
                if ($type = $fields[$key]) {
                    // Metadata exists for this value, trigger event
                    // to cast value to object etc.
                    $castEvent = new Event('preHydrate.cast', $value, array('type' => $type));
                    $castResult = $this->getModelManager()->getEventManager()->trigger($castEvent);

                    // Check if the Event called stopPropagation
                    if ($castResult->stopped()) {
                        // Store returned result back in data array
                        $data[$key] = $castResult->last();
                    }
                }
            }

            // Set data
            $event->setParam('data', $data);
        }
    }

    /**
     * @param Event $event
     */
    public function hydrate(Event $event)
    {
        $target = $event->getTarget();
        $data = $event->getParam('data');

        $hydrator = new ObjectProperty();
        $hydrator->hydrate($data, $target);
    }

    /**
     * @param Event $event
     */
    public function postHydrate(Event $event)
    {

    }

    /**
     * Check if supplied data is a datetime column.
     * If so, cast to \DateTime.
     *
     * @param Event $event
     * @return \Datetime|object|string
     */
    public function castDateTime(Event $event)
    {
        $value = $event->getTarget();
        $type = $event->getParam('type');

        // Check if this is a datetime column
        if ('datetime' == strtolower($type)) {
            // Turn value into timestamp
            if (false !== ($time = strtotime($value))) {
                // Create new DateTime with timestamp
                $dt = new \Datetime();
                $dt->setTimestamp($time);

                // All done, stop all other events and return
                $event->stopPropagation(true);
                return $dt;
            }
        }

        // Return original just incase
        return $value;
    }
}
