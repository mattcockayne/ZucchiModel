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

use Zucchi\Traits\TraitsUtils;
use ZucchiModel\Model\Manager;
use ZucchiModel\Annotation;

/**
 * Behaviour Listener
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
     * @var Manager
     */
    protected $modelManager;

    /**
     * Constructor
     *
     * @param Manager $modelManager
     */
    public function __construct(Manager $modelManager)
    {
        $this->setModelManager($modelManager);
    }

    /**
     * Set Model Manager
     *
     * @param Manager $modelManager
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
     * @return Manager
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
        // Get all traits for given class target
        $traits = TraitsUtils::getTraits($target);

        // Check if target uses ChangeTrackingTrait
        if (in_array('ZucchiModel\Behaviour\ChangeTrackingTrait', $traits)) {
            $target->setCleanData(get_object_vars($target));
        }
    }
}
