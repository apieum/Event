<?php
/**
 * File Listener.php
 *
 * PHP version 5.2
 *
 * @category Classes
 * @package  Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Event_Listener.php
 *
 */

require_once __DIR__.DIRECTORY_SEPARATOR.'Interface'.DIRECTORY_SEPARATOR.'Listener.php';
/**
 * Execute an action when an event is fired and log them with the defined logger.
 * 
 * @category Classes
 * @package  Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Event_Listener
 *
 */
class Event_Listener implements Event_Listener_Interface
{
    protected $stopPropagation = false;
    protected $propagationStopper;
    protected $logs            = array();
    protected $actions         = array();
    protected $logger;
    /**
     * Constructor 
     * 
     * @param bool $prevent whether to stop propagation
     * @param func $logger  a function/method to call for logging
     */
    public function __construct($prevent=false, $logger=null)
    {
        $this->preventPropagation($prevent);
        $this->setLogger($logger);
    }
    /**
     * fire the event $event by calling the binded method with arguments $params
     * return the result of the action, wich should be a boolean that telling
     * if all was done as expected, but you're free to return what you want.
     * 
     * @param string $event  the event to trigger; should be a string
     * @param array  $params arguments to send to event action
     * 
     * @return bool|mixed result of action, should be a boolean
     */
	public function fire($event, $params=array())
	{
	    $action = $this->getEventAction($event, $params);
	    $result = call_user_func_array($action, $params);
	    $this->logs[] = call_user_func($this->logger, $event, $params, $result);
	    $this->stopPropagation();
	    return $result;
	}
	/**
	 * Set if the event must be stopped after this call 
	 * 
	 * @param bool $prevent whether or not to prevent propagation of events
	 * 
	 * @return object this for chaining
	 */
	public function preventPropagation($prevent = true)
	{
	    $this->stopPropagation = (bool) $prevent;
	    return $this;
	}
	/**
	 * return true if propagation must be stopped otherwise false
	 * 
	 * @return bool
	 */
	public function stopPropagation()
	{
	    if ($this->stopPropagation && !is_null($this->propagationStopper)) {
	        call_user_func($this->propagationStopper);
	    }
	    return $this->stopPropagation;
	}
	/**
	 * Set an action to call for a given event 
	 * 
	 * @param string $event  event name should be a string
	 * @param func   $action a function or method to call
	 * 
	 * @throws InvalidArgumentException if action is not callable
	 * @return object this for chaining
	 */
	public function setEventAction($event, $action)
	{
	    if (!is_callable($action)) {
	        $msg = sprintf("'%s' action must be callable.", $event);
	        throw new InvalidArgumentException($msg);
	    }
	    $this->actions[$event] = $action;
	    return $this;
	}
	/**
	 * return the default action : a method of this object that start with 'on'
	 * and ends with the event name to camel case.
	 * 
	 * @param string $event  event, should be a string
     * @param array  $params arguments to send to event action
	 * 
	 * @return array|null a callable function or null
	 */
	public function getDefaultAction($event, $params=array())
	{
	    $eventAction = 'on'.str_replace(' ', '', ucwords($event));
	    if (method_exists($this, $eventAction)) {
	        return array($this, $eventAction);
	    } else {
	        throw new LogicException($this->eventLog($event, $params, false));
	    }
	}
	/**
	 * return the action for an event, if not defined return default action.
	 *  
	 * @param string $event  should be a string
     * @param array  $params arguments to send to event action
	 * 
	 * @return func a callable function/method
	 */
	public function getEventAction($event, $params=array()) 
	{
	    if (isset($this->actions[$event])) {
	        return $this->actions[$event];
	    } else {
	        return $this->getDefaultAction($event, $params);
	    }
	}
	/**
	 * Return the string log for an event
	 * 
	 * @param string $event  event name
	 * @param array  $params parameters used to call the action for event
	 * @param bool   $result result of call, should be a boolean
	 * 
	 * @return string the datas to append logs.
	 */
	protected function eventLog($event, $params, $result)
	{
	    if ($result === false) {
	        $msg = "Error : no action found for event '%s' with parameters '%s'";
	    } else {
	        $msg = "Event '%s' called with parameters '%s', return '%s'";
	        $result = serialize($result);
	    }
	    $params = serialize($params);
	    return sprintf($msg, $event, $params, $result);
	}
	/**
	 * Set the function/method to call for logging, 
	 * if not callable logEvent method is used 
	 * 
	 * @param func $logger a function or method that will be called to log
	 * 
	 * @return object this for chaining
	 */
	public function setLogger($logger)
	{
	    if (is_callable($logger)) {
            $this->logger = $logger;
        } else {
            $this->logger = array($this, 'eventLog');
        }
        return $this;
	}
	/**
	 * return logs
	 *  
	 * @return array
	 */
	public function getLogs()
	{
	    return $this->logs;
	}
	/**
	 * set how to stop propagation by calling the given a function/method $action
	 * 
	 * @param func $action function or method to call to stop propagation
	 * 
	 * @throws InvalidArgumentException if $action not callable
	 * @return object $this for chaining
	 */
	public function setHowToStopPropagation($action)
	{
	    if (is_callable($action)) {
	        $this->propagationStopper = $action;
	    } else {
	        $message = sprintf("'%s' not callable", $action);
	        throw new InvalidArgumentException($message);
	    }
	    return $this;
	}
	/**
	 * return a hash of the object, used in comparison by array functions
	 * 
	 * @return string a hash of this
	 */
	public function __toString()
	{
	    return spl_object_hash($this);
	}
}