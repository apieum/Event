<?php
/**
 * File Listener.php
 *
 * PHP version 5.2
 *
 * @category Interfaces
 * @package  Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Event_Listener.php
 *
 */
/**
 * This interface is required by dispatcher
 * 
 * @category Interfaces
 * @package  Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Event_Listener_Interface
 *
 */
interface Event_Listener_Interface
{
    
	/**
	 * return true if propagation must be stopped otherwise false
	 * 
	 * @return bool
	 */
	public function stopPropagation();
	
    /**
     * fire the event $event with parameters $params
     * 
     * @param string $event  the event to fire; should be a string
     * @param array  $params a list of parameters
     * 
     * @return bool|mixed result of event action, should be a boolean
     */
	public function fire($event, $params=array());
}