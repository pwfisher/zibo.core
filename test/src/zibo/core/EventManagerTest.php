<?php

namespace zibo\core;

use zibo\library\Callback;

use zibo\test\BaseTestCase;
use zibo\test\Reflection;

use \Exception;

class EventManagerTest extends BaseTestCase {

    /**
     * @var EventManager
     */
    private $eventManager;

    private $executed;

    protected function setUp() {
        $this->eventManager = new EventManager();
    }

    public function testConstructWithMaxEvents() {
        $maxEventListeners = 10;
        $eventManager = new EventManager($maxEventListeners);
        $this->assertEquals($maxEventListeners, Reflection::getProperty($eventManager, 'maxEventListeners'));
        $this->assertEquals(EventManager::DEFAULT_MAX_EVENT_LISTENERS, Reflection::getProperty($this->eventManager, 'maxEventListeners'));
    }

    /**
     * @dataProvider providerConstructWithInvalidMaxEventsThrowsException
     */
    public function testConstructWithInvalidMaxEventsThrowsException($maxEvents) {
        try {
            new EventManager($maxEvents);
            $this->fail();
        } catch (Exception $e) {

        }
    }

    public function providerConstructWithInvalidMaxEventsThrowsException() {
        return array(
            array(0),
            array(-15),
            array('test'),
            array($this),
        );
    }

    public function testRegisterEvent() {
        $event = 'event';
        $callback = array($this, 'testRegisterEvent');

        $this->eventManager->registerEventListener($event, $callback);

        $events = Reflection::getProperty($this->eventManager, 'events');

        $this->assertTrue(in_array(new Callback($callback), $events[$event]));
    }

    /**
     * @dataProvider providerRegisterEventWithInvalidNameThrowsException
     */
    public function testRegisterEventWithInvalidNameThrowsException($name) {
        try {
            $this->eventManager->registerEventListener($name, array('instance', 'method'));
            $this->fail();
        } catch (Exception $e) {

        }
    }

    public function providerRegisterEventWithInvalidNameThrowsException() {
        return array(
            array(null),
            array(''),
            array($this),
        );
    }

    public function testRegisterEventWithExistingWeightThrowsException() {
        $event = 'event';
        $callback = array('instance', 'method');

        $this->eventManager->registerEventListener($event, $callback, 20);

        try {
            $this->eventManager->registerEventListener($event, $callback, 20);
            $this->fail();
        } catch (Exception $e) {

        }
    }

    /**
     * @dataProvider providerRegisterEventWithInvalidWeightThrowsException
     */
    public function testRegisterEventWithInvalidWeightThrowsException($weight) {
        try {
            $this->eventManager->registerEventListener('event', array('instance', 'method'), $weight);
            $this->fail();
        } catch (Exception $e) {

        }
    }

    public function providerRegisterEventWithInvalidWeightThrowsException() {
        return array(
            array('test'),
            array($this),
            array(70000),
        );
    }

    public function testClearEventListenersForEvent() {
        $event = 'event';
        $callback = array($this, 'testClearEventListeners');

        $this->eventManager->registerEventListener($event, $callback);

        $events = Reflection::getProperty($this->eventManager, 'events');
        $this->assertTrue(in_array(new Callback($callback), $events[$event]));

        $this->eventManager->clearEventListeners($event);

        $events = Reflection::getProperty($this->eventManager, 'events');
        $this->assertFalse(isset($events[$event]));
    }

    public function testTriggerEventWithNoCallbacks() {
        $this->eventManager->triggerEvent('test');
    }

    public function testTriggerEventWithEvents() {
        $event = 'event';
        $this->executed = false;
        $callback = array($this, 'eventCallbackMethod');

        $this->eventManager->registerEventListener($event, $callback);
        $this->eventManager->triggerEvent($event);

        $this->assertTrue($this->executed, 'TestEvent has not been called');
    }

    public function testTriggerEventWithArguments() {
        $event = 'event';
        $this->executed = 0;
        $callback = array($this, 'eventCallbackMethodSum');

        $this->eventManager->registerEventListener($event, $callback);
        $this->eventManager->triggerEvent($event, 1);
        $this->eventManager->triggerEvent($event, 2);

        $this->assertEquals(3, $this->executed);
    }

    public function testTriggerEventWithWeights() {
        $event = 'event';
        $this->executed = 10;
        $callback1 = array($this, 'eventCallbackMethod');
        $callback2 = array($this, 'eventCallbackMethodSum');
        $callback3 = array($this, 'eventCallbackMethodMultiply');
        $callback4 = array($this, 'eventCallbackMethodSubstract');

        $this->eventManager->registerEventListener($event, $callback3);
        $this->eventManager->registerEventListener($event, $callback1, 20);
        $this->eventManager->registerEventListener($event, $callback4, 99);
        $this->eventManager->registerEventListener($event, $callback2, 10);
        $this->eventManager->triggerEvent($event, 7);

        // 1: 10 + 7 = 17
        // 2: 7
        // 3: 7 * 7 = 49
        // 4: 49 - 7 = 42

        $this->assertEquals(42, $this->executed);
    }

    /**
     * @dataProvider providerRunEventWithInvalidEventThrowsException
     */
    public function testRunEventWithInvalidEventThrowsException($event) {
        try {
            $this->eventManager->triggerEvent($event);
            $this->fail();
        } catch (Exception $e) {

        }
    }

    public function providerRunEventWithInvalidEventThrowsException() {
        return array(
            array(''),
            array($this),
        );
    }

    public function eventCallbackMethod($value = true) {
       $this->executed = $value;
    }

    public function eventCallbackMethodSum($value) {
       $this->executed += $value;
    }

    public function eventCallbackMethodSubstract($value) {
       $this->executed -= $value;
    }

    public function eventCallbackMethodMultiply($value) {
       $this->executed *= $value;
    }

}