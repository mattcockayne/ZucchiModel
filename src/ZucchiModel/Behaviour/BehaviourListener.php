<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModel\Behaviour;

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
class BehaviourListener
{
    /**
     * List of attached events.
     *
     * @var array
     */
    protected $listeners = array();

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
            $events->attach('postHydrate', array($this, 'setCleanData')),
        );
    }

    /**
     * Remove listeners from events
     *
     * @param EventManagerInterface $events
     */
    public function detach(EventManagerInterface $events)
    {
        array_walk($this->listeners, array($events, 'detach'));
        $this->listeners = array();
    }

    /**
     * Call ChangeTrackingTrait
     *
     * @param Event $event
     */
    public function setCleanData(Event $event)
    {
        $target = $event->getTarget();
        if (in_array('ChangeTrackingTrait', class_uses($target))) {
            $target->setCleanData($event->getData());
        }
    }
}
