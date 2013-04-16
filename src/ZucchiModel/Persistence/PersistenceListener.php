<?php
/**
 * PeristenceListener.php - (http://zucchi.co.uk) 
 *
 * @link      http://github.com/zucchi/{PROJECT_NAME} for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\Persistence;

/**
 * PeristenceListener
 *
 * Description of class
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel\Persistence
 * @subpackage 
 * @category 
 */
class PersistenceListener
{
    /**
     * List of attached events.
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * Attach listeners
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners = array(
            $events->attach('prePersist', array($this, 'prePersist')),
            $events->attach('postPersist', array($this, 'postPersist')),
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
     * @param Event $event
     */
    public function prePersist(Event $event)
    {

    }

    public function postPersist(Event $event)
    {

    }
}