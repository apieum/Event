<?php
/**
 * File Event_ListenerTests.php
 *
 * PHP version 5.2
 *
 * @category Tests
 * @package  Tests/Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Listener.php
 *
 */
$libDir = str_replace('tests'.DIRECTORY_SEPARATOR, '', __DIR__);
require_once $libDir.DIRECTORY_SEPARATOR.'Listener.php';
/**
 * Test class for Event_Listener.
 * 
 * @category Tests
 * @package  Tests/Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Event_ListenerTest
 *
 */
class Event_ListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Event_Listener
     */
    protected $object;
    protected $stopped = false;

    /**
     * Sets up the fixture.
     * 
     * @return null
     */
    protected function setUp()
    {
        $this->object = new Event_Listener();
    }
    /**
     * event default action is method getDefaultAction
     * 
     * @return @test
     */
    public function eventDefaultActionThrowALogicException()
    {
        try {
            $this->object->getEventAction('event1');
        } catch (LogicException $logicExc1) {
            $this->assertContains('event1', $logicExc1->getMessage());
        }
        $this->assertNotNull($logicExc1);
    }
    /**
     * event default action is called by fire
     * 
     * @return @test
     */
    public function eventDefaultActionIsCalledByFire()
    {
        try {
            $this->object->fire('event2');
        } catch (LogicException $logicExc2) {
            $this->assertContains('event2', $logicExc2->getMessage());
        }
        $this->assertNotNull($logicExc2);
    }
    /**
     * when setting an actino not callable throw InvalidArgumentException
     * 
     * @return @test
     */
    public function whenSettingAnActionNotCallableThrowInvalidargumentException()
    {
        try {
            $this->object->setEventAction('event3', 'void');
        } catch (InvalidArgumentException $invalidExc) {
            $this->assertContains('event3', $invalidExc->getMessage());
        }
        $this->assertNotNull($invalidExc);
    }
    /**
     * Can set one callable action for each event
     * 
     * @return @test
     */
    public function canSetOneCallableActionForEachEvent()
    {
        $this->object->setEventAction('event4', 'ucfirst');
        $this->assertAttributeEquals(
            array('event4'=>'ucfirst'), 'actions', $this->object
        );
        $this->object->setEventAction('event4', 'ucwords');
        $this->assertAttributeEquals(
            array('event4'=>'ucwords'), 'actions', $this->object
        );
    }
    /**
     * event action is called by fire
     * 
     * @return @test
     */
    public function eventActionIsCalledByFire()
    {
        $sentence = array('this is a sentence.');
        $this->object->setEventAction('event5', 'ucfirst');
        $result = $this->object->fire('event5', $sentence);
        $this->assertEquals('This is a sentence.', $result);
        $this->object->setEventAction('event5', 'ucwords');
        $result = $this->object->fire('event5', $sentence);
        $this->assertEquals('This Is A Sentence.', $result);
    }
    /**
     * each event is logged with event name, parameters and result
     * 
     * @return @test
     */
    public function eachEventIsLoggedWithNameParametersAndResult()
    {
        $this->assertEquals(array(), $this->object->getLogs());
        $this->object->setEventAction('event6', 'ucfirst');
        $result = $this->object->fire('event6', array('word'));
        $logs   = $this->object->getLogs();
        $this->assertContains('event6', $logs[0]);
        $this->assertContains($result, $logs[0]);
        $this->assertContains('word', $logs[0]);
        
    }
    /**
     * can set a function to log events
     * 
     * @return @test
     */
    public function canSetAFunctionToLogEvents()
    {
        $this->object->setLogger(create_function('', 'return func_get_args();'));
        $this->object->setEventAction('event7', 'ucfirst');
        $result = $this->object->fire('event7', array('word'));
        $logs   = $this->object->getLogs();
        $expect = array('event7', array('word'), 'Word');
        $this->assertEquals($expect, $logs[0]);
    }
    /**
     * if function not callable when setting logger method eventlog is the logger
     * 
     * @return @test
     */
    public function ifLoggerNotCallableWhenSettingItMethodEventlogIsUsed()
    {
        $this->object->setLogger('undefined');
        $expected = array($this->object, 'eventLog');
        $this->assertAttributeEquals($expected, 'logger', $this->object);
    }
    /**
     * by default listener tells to continue event propagation
     * 
     * @return @test
     */
    public function byDefaultListenerTellsToContinuePropagation()
    {
        $this->assertFalse($this->object->stopPropagation());
    }
    /**
     * listerner knows if propagation must stop
     * 
     * @return @test
     */
    public function listenerKnowsIfEventPropagationMustStop()
    {
        $this->object->preventPropagation();
        $this->assertTrue($this->object->stopPropagation());
        $this->object->preventPropagation(false);
        $this->assertFalse($this->object->stopPropagation());
    }
    /**
     * can use inheritance to set default events actions
     * 
     * @return @test
     */
    public function canUseInheritanceToSetDefaultEventsActions()
    {
        include_once 'UcFirstListener.php';
        $listener = new UcFirstListener();
        $result   = $listener->fire('uppercase', array('a word'));
        $this->assertEquals('A Word', $result);
        $result   = $listener->fire('uc first', array('a word'));
        $this->assertEquals('A word', $result);
    }
    /**
     * Help to simulate stopPropagation
     * 
     * @return null
     */
    public function stopPropagation()
    {
        $this->stopped = true;
    }
    /**
     * can set how to stop propagation
     * 
     * @return @test
     */
    public function canSetHowToStopPropagation()
    {
        $propagationStopper = array($this, 'stopPropagation');
        $this->object->setHowToStopPropagation($propagationStopper);
        $this->assertAttributeEquals(
            $propagationStopper, 'propagationStopper', $this->object
        );
    }
    /**
     * if propagationStopper not callable when setting it throw exception
     * 
     * @return @test 
     */
    public function throwInvalidArgumentWhenSettingAPropagationStopperNotCallable()
    {
        try {
            $this->object->setHowToStopPropagation('undefined');
        } catch (InvalidArgumentException $invalidArgExc) {
            $this->assertContains('undefined', $invalidArgExc->getMessage());
        }
        $this->assertTrue(isset($invalidArgExc), 'Exception not thrown');
    }
    /**
     * call propagationStopper when it's set, event is fired and result not false
     * 
     * @return @test
     */
    public function callPropagationStopperWhenItSSetAndEventTriggered()
    {
        $event = 'test propagation stop';
        $this->object->setEventAction($event, create_function('', 'return true;'));
        $this->object->fire($event);
        $this->assertFalse($this->stopped);
        $this->object->preventPropagation();
        $this->object->fire($event);
        $this->assertFalse($this->stopped);
        $this->object->setHowToStopPropagation(array($this, 'stopPropagation'));
        $this->object->fire($event);
        $this->assertTrue($this->stopped);
        $this->stopped = false;
        $this->object->preventPropagation(false);
        $this->object->fire($event);
        $this->assertFalse($this->stopped);
    }
    /**
     * object can be converted to string
     * 
     * @return @test
     */
    public function objectCanBeConvertedToString()
    {
        $this->assertTrue(is_string((string) $this->object));
    }
}
?>
