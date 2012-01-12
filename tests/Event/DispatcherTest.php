<?php
/**
 * File Event_DispatcherTests.php
 *
 * PHP version 5.2
 *
 * @category Tests
 * @package  Tests/Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Event_Dispatcher.php
 *
 */
$libDir = str_replace('tests'.DIRECTORY_SEPARATOR, '', __DIR__);
require_once $libDir.DIRECTORY_SEPARATOR.'Dispatcher.php';
require_once $libDir.DIRECTORY_SEPARATOR.'Listener.php';
/**
 * Test class for Event_Dispatcher.
 * 
 * @category Tests
 * @package  Tests/Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Event_Dispatcher
 *
 */
class Event_DispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Event_Dispatcher
     */
    protected $object;

    /**
     * Sets up the fixture.
     * 
     * @return null
     */
    protected function setUp()
    {
        $this->object = new Event_Dispatcher;
    }
    /**
     * Listeners must implements Event_Listener interface
     * 
     * @return @test
     */
    public function listenersMustImplementsEventListenerInterface()
    {
        $listener = new Event_Listener();
        $this->assertTrue($this->object->accept($listener));
        $listener = new ArrayObject(array());
        $this->assertFalse($this->object->accept($listener));
    }
    /**
     * can bind a listener to multiple events
     * 
     * @return @test
     */
    public function canBindAListenerToMultipleEvents()
    {
        $listener = new Event_Listener();
        $this->assertFalse($this->object->contains($listener));
        $this->object->bind($listener, array('event0', 'event1'));
        $this->assertTrue($this->object->contains($listener));
        $expect = array($listener);
        $this->assertEquals($expect, $this->object->getListenersFor('event0'));
        $this->assertEquals($expect, $this->object->getListenersFor('event1'));
    }
    /**
     * can bind multiple listeners to multiple events
     * 
     * @return @test
     */
    public function canBindMultipleListenersToMultipleEvents()
    {
        $listener1 = new Event_Listener();
        $listener2 = new Event_Listener();
        $listeners = array($listener1, $listener2);
        $this->assertFalse($this->object->contains($listener1));
        $this->assertFalse($this->object->contains($listener2));
        $this->object->bind($listeners, array('event0', 'event1'));
        $this->assertTrue($this->object->contains($listener1));
        $this->assertTrue($this->object->contains($listener2));
        $this->assertEquals($listeners, $this->object->getListenersFor('event0'));
        $this->assertEquals($listeners, $this->object->getListenersFor('event1'));
    }
    /**
     * can bind listeners to all events
     * 
     * @return @test
     */
    public function canBindListenersToAllEvents()
    {
        $listener = new Event_Listener();
        $this->object->bindAll($listener);
        $this->assertTrue($this->object->contains($listener));
        $expect = array($listener);
        $this->assertEquals($expect, $this->object->getListenersFor('event'));
        $this->assertEquals($expect, $this->object->getListenersFor('other'));
        $this->assertEquals($expect, $this->object->getListenersFor('any'));
    }
    /**
     * duplicates are removed when getting event listeners
     * 
     * @return @test
     */
    public function duplicatesAreRemovedWhenGettingEventListeners()
    {
        $listeners = array(new Event_Listener());
        $this->object->bindAll($listeners);
        $this->assertEquals($listeners, $this->object->getListenersFor('event'));
        $this->object->bind($listeners, array('event'));
        $this->assertEquals($listeners, $this->object->getListenersFor('event'));
        
    }
    /**
     * duplicates are removed when setting listeners for all events
     * 
     * @return @test
     */
    public function duplicatesAreRemovedWhenSettingListenersForAllEvents()
    {
        $listeners = array(new Event_Listener());
        $this->object->bindAll($listeners);
        $this->object->bindAll($listeners);
        $this->assertAttributeSame($listeners, 'allEvents', $this->object);
    }
    /**
     * can unbind listeners from choosen events
     * 
     * @return @test
     */
    public function canUnbindListenersFromChoosenEvents()
    {
        $listeners = array(new Event_Listener());
        $this->object->bind($listeners, array('event0', 'event1', 'event2'));
        $this->object->unbind($listeners, array('event0', 'event1'));
        $this->assertSame(array(), $this->object->getListenersFor('event0'));
        $this->assertSame(array(), $this->object->getListenersFor('event1'));
        $this->assertSame($listeners, $this->object->getListenersFor('event2'));
    }
    /**
     * can unbind listeners from all events
     * 
     * @return @test
     */
    public function canUnbindListenersFromAllEvents()
    {
        $listener = new Event_Listener();
        $this->object->bind($listener, array('event0', 'event1'));
        $this->object->bindAll($listener);
        $this->assertTrue($this->object->contains($listener));
        $this->object->unbindAll($listener);
        $this->assertFalse($this->object->contains($listener));
    }
    /**
     * when trigger an event, listeners fire method is called
     * 
     * @return @test
     */
    public function whenTriggerAnEventListernerFireMethodIsCalled()
    {
        $listener1 = $this->getMock('Event_Listener', array('fire'));
        $listener1->expects($this->once())
            ->method('fire')
            ->with(
                $this->equalTo('event1'),
                $this->equalTo(array('param 1'))
            );
        $listener2 = $this->getMock('Event_Listener', array('fire'));
        $listener2->expects($this->at(0))
            ->method('fire')
            ->with(
                $this->equalTo('event1'),
                $this->equalTo(array('param 1'))
            );
        $listener2->expects($this->at(1))
            ->method('fire')
            ->with(
                $this->equalTo('event2'),
                $this->equalTo(array('param 2'))
            );
        $this->object->bind($listener1, array('event1'));
        $this->object->bindAll($listener2);
        $this->object->trigger('event1', array('param 1'));
        $this->object->trigger('event2', array('param 2'));
    }
    /**
     * when trigger if result and stopPropagation are True trigger stops
     * 
     * @return @test
     */
    public function whenTriggerIfResultNotFalseAndStopPropagationThenTriggerStops()
    {
        $listener1 = new Event_Listener();
        $listener1->setEventAction('event1', create_function('', 'return false;'));
        $listener2 = $this->getMock('Event_Listener', array('fire'));
        $listener2->expects($this->exactly(2))
            ->method('fire')
            ->with(
                $this->equalTo('event1'),
                $this->equalTo(array('param 1'))
            );
        $this->object->bindAll(array($listener1, $listener2));
        $this->object->trigger('event1', array('param 1'));
        $listener1->preventPropagation();
        $this->object->trigger('event1', array('param 1'));
        $listener1->setEventAction('event1', create_function('', 'return true;'));
        $this->object->trigger('event1', array('param 1'));
    }
}
?>
