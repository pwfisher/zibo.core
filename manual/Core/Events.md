Zibo has a simple but powerful event manager for inserting dynamic logic or changing system behaviour.

Events are triggered by name and can be dispatched to functions and/or method calls. 
These are called the event listeners. Any valid PHP callback can work as a event listener.

Event names are _._ (dot) separated strings, preferably a more general token first (eg. _core_, _database_) and defining further down the line (eg _core.dispatch.pre_).

## Trigger A Event

A event can be triggered with a simple call to Zibo:

    $zibo->triggerEvent('event.name', $argument1, $argument2);
    
All listeners to the event _event.name_ are now triggered. 
The arguments for the event are the same as passed to the trigger call but the event name is replaced with the Zibo instance.

A listener for this sample event could look like:

    function foo($zibo, $argument1, $argument2) {
    ...
    }
    
However, we encourage to define your listeners with a proper signature and in a class:

    use zibo\core\Zibo;
    
    use zibo\library\xmlrpc\Server;
    ...
    class Foo {
        ...
        public function preXmlRpcService(Zibo $zibo, Server $server) {
            ...
        }
        ...
    }

## Register A Event Listener

You can register a event listener to Zibo using the call:

    $zibo->registerEventListener('event.name', array($instance, 'method'));

Event listeners are executed in the order they are registered. 
It's best to have listeners which are independant of each other.

However, sometimes it's interesting to influence the order of the listeners.
To achieve this, you can pass a index to the registration of your listener. 
Indexes range from 0 to 100. 
New listeners without a index will be added from 50 onwards.
This gives enough room before and after the default index.

In the following example, _$bar->method()_ would be triggered before _$foo->method()_ when the event _event.name_ is triggered:

    $zibo->registerEventListener('event.name', array($foo, 'method'));
    $zibo->registerEventListener('event.name', array($bar, 'method'), 10);
    
    $zibo->triggerEvent('event.name');
    
In the following example, _$foo->methodC()_ would be triggered first, then _$foo->methodA()_, _$bar->methodD()_ and finally _$bar->methodB()_:

    $zibo->registerEventListener('event.name', array($foo, 'methodA'));
    $zibo->registerEventListener('event.name', array($bar, 'methodB'), 70);
    $zibo->registerEventListener('event.name', array($foo, 'methodC'), 10);
    $zibo->registerEventListener('event.name', array($bar, 'methodD'));

    $zibo->triggerEvent('event.name');
    
Events are generally registered in a module class. 
See the Modules page for more information.

## Builtin Events

The following events are builtin the Zibo core. System events, event names starting with _core._, can only be triggered from within the Zibo instance.

* __core.exception__  
When a uncatched exception is thrown, the _core.error_ event is triggered with the exception as parameter.
If there are no listeners to this event, a default exception page is generated.   
Listen to this event for custom error pages.

* __core.route.pre__  
You can use this event to influence the routing of Zibo. 
Override default action, override the router, ...  
The HTTP request is passed as parameter but it's read only. 
You cannot change the incoming request. 

* __core.route.post__  
This event is triggered after the routing is handled and before the dispatching starts. 
You can modify the incoming MVC request, which will be dispatched, here.

* __core.dispatch.pre__  
In the request chaining of the dispatch procedure, this event is triggered before dispatching a request.
You can modify the incoming and the chained MVC requests before they are dispatched here.

* __core.dispatch.post__  
In the request chaining of the dispatch procedure, this event is triggered after dispatching a request.
You can remove a chained request here.

* __core.response.pre__  
Using this event, you can process the response which will be sent as output. 
The reponse instance is provided as argument.

* __core.response.post__  
Event to clean up temporary files or whatever you need to do after sending the response.