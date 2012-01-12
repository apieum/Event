<?php
/**
 * File Dispatcher.php
 *
 * PHP version 5.2
 *
 * @category Interfaces
 * @package  Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Event_Dispatcher.php
 *
 */

/**
 * Contains event listeners bound within events they observe
 * 
 * @category Interfaces
 * @package  Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Event_Dispatcher
 *
 */
interface Event_Dispatcher_Interface
{

    /**
     * Fire event $event on all binded listeners
     * 
     * @param string $event  the event to fire
     * @param array  $params parameters to send to listener
     * 
     * @return object this for chaining
     */
    public function trigger($event, $params=array());
    /**
     * Attach an event listener for all events
     *  
     * @param array|object $listeners event listener or a list of event listener 
     * 
     * @return object this for chaining
     */
    public function bindAll($listeners);
    /**
     * Attach an listener for events $events
     *  
     * @param array|object $listeners event listener or a list of event listener 
     * @param array        $events    a list of event
     * 
     * @return object this for chaining
     */
    public function bind($listeners, $events=array());
    /**
     * Detach event listeners from all events
     *  
     * @param array|object $listeners event listener or a list of event listener 
     * 
     * @return object this for chaining
     */
    public function unbindAll($listeners);
    /**
     * Detach listeners for events $events
     *  
     * @param array|object $listeners event listener or a list of event listener 
     * @param array        $events    a list of event
     * 
     * @return object this for chaining
     */
    public function unbind($listeners, $events=array());
    /**
     * Return if a listener is bound to any event
     * 
     * @param string $listener an event listener
     * 
     * @return bool
     */
    public function contains($listener);
}