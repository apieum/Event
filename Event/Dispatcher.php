<?php
/**
 * File Dispatcher.php
 *
 * PHP version 5.2
 *
 * @category Classes
 * @package  Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Event_Dispatcher.php
 *
 */
require_once __DIR__.DIRECTORY_SEPARATOR.'Interface'.DIRECTORY_SEPARATOR.'Dispatcher.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'Interface'.DIRECTORY_SEPARATOR.'Listener.php';
/**
 * Contains event listeners bound within events they observe
 * 
 * @category Classes
 * @package  Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Event_Dispatcher
 *
 */
class Event_Dispatcher implements Event_Dispatcher_Interface
{

    protected $listeners= array();
    protected $allEvents= array();
    protected $events   = array();
    /**
     * return an array of listeners from given argument 
     * 
     * @param array|object $listeners object or list of objects with fire method
     * 
     * @return array
     */
    protected function listListeners($listeners)
    {
        if (!is_array($listeners)) {
            return $this->accept($listeners) ? array($listeners) : array(); 
        }
        $listeners = array_filter($listeners, array($this, 'accept'));
        return self::arrayUniqueValues($listeners);
    }
    /**
     * return an array with unique values
     * keys are modified
     * 
     * @param array $array the array to deduplicate
     * 
     * @return array
     */
    public static function arrayUniqueValues(array $array)
    {
        $return = array();
        while (!is_null($current = array_shift($array))) {
            $keys     = array_flip(array_keys($array, $current, true));
            $array    = array_diff_key($array, $keys);
            $return[] = $current;
        }
        return $return;
    }
    /**
     * Return if a listener can be accepted
     * 
     * @param object $listener a listener to test
     * 
     * @return bool
     */
    public function accept($listener)
    {
        return $listener instanceof Event_Listener_Interface;
    }
    /**
     * Fire event $event on all binded listeners
     * 
     * @param string $event  the event to fire
     * @param array  $params parameters to send to listener
     * 
     * @return object this for chaining
     */
    public function trigger($event, $params=array()) 
    {
        $listeners = $this->getListenersFor($event);
        foreach ($listeners as $listener) {
            $result = $listener->fire($event, $params);
            if ($result !== false && $listener->stopPropagation()) {
                return $this;
            }
        }
        return $this;
    }
    /**
     * Attach an event listener for all events
     *  
     * @param array|object $listeners event listener or a list of event listener 
     * 
     * @return object this for chaining
     */
    public function bindAll($listeners)
    {
        $listeners = $this->listListeners($listeners);
        $this->allEvents = array_merge($this->allEvents, $listeners);
        $this->allEvents = self::arrayUniqueValues($this->allEvents);
        return $this;
    }
    /**
     * Attach an listener for events $events
     *  
     * @param array|object $listeners event listener or a list of event listener 
     * @param array        $events    a list of event
     * 
     * @return object this for chaining
     */
    public function bind($listeners, $events=array())
    {
        $listeners = $this->listListeners($listeners);
        $events = array_fill_keys($events, $listeners);
        $this->events = array_merge_recursive($this->events, $events);
        return $this;
    }
    /**
     * Detach event listeners from all events
     *  
     * @param array|object $listeners event listener or a list of event listener 
     * 
     * @return object this for chaining
     */
    public function unbindAll($listeners)
    {
        $listeners = $this->listListeners($listeners);
        $this->allEvents = array_diff($this->allEvents, $listeners);
        $events = array_keys($this->events);
        return $this->unbind($listeners, $events);
    }
    /**
     * Detach listeners for events $events
     *  
     * @param array|object $listeners event listener or a list of event listener 
     * @param array        $events    a list of event
     * 
     * @return object this for chaining
     */
    public function unbind($listeners, $events=array())
    {
        $listeners = $this->listListeners($listeners);
        $events = array_intersect(array_keys($this->events), $events);
        foreach ($events as $event) {
            $this->events[$event] = array_diff($this->events[$event], $listeners);
            // reorder keys (and remove duplicates entries)
            $this->events[$event] = self::arrayUniqueValues($this->events[$event]);
        }
    }
    /**
     * Return if a listener is bound to any event
     * 
     * @param string $listener an event listener
     * 
     * @return bool
     */
    public function contains($listener)
    {
        $bound = in_array($listener, $this->allEvents, true);
        $events = array_keys($this->events);
        while ($bound === false && !is_null($event = array_shift($events))) {
            $bound = in_array($listener, $this->events[$event], true);
        }
        return $bound;
    }
    /**
     * Return all listeners for a given event 
     * 
     * @param string $event the event name
     * 
     * @return array a list of objects Ids bound to event $event
     */
    public function getListenersFor($event)
    {
        $listeners = $this->allEvents;
        if (isset($this->events[$event])) {
            $listeners = array_merge($listeners, $this->events[$event]);
        }
        return self::arrayUniqueValues($listeners);
    }
}