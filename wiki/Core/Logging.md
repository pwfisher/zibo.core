Log messages can help you track and debug the flow of your application.

There are 4 levels of log messages:

* __error (1)__:    
Messages of events which cause the system to crash (exceptions, ...)
* __warning (2)__:   
Messages of events which will probably cause the system to crash (required file missing, ...)
* __information (4)__:  
Messages of normal significant events of the system (mail sent, user added, ...)
* __debug (8)__:  
Messages of normal insignificant events of the application (template rendered, event triggered, ...)

## Logging A Message

The log can be obtained from the Zibo instance. 
It's not always set, so expect the possibility for a _null_ value as logger.

    $log = $zibo->getLog();
    if ($log) {
        $log->logInformation('Log message', 'an optional description', 'name of the log source');
    }
    
You can also easily log exceptions:

    try {
        // some buggy code
    } catch (Exception $exception) {
        $log = $zibo->getLog();
        if ($log) {
            $log->logException($exception);
        }
        
        // handle exception
    }

For a full overview of the log methods, check the API of the [zibo\library\log\Log](/api/class/zibo/library/log/Log) class.

## Log Listeners

By default, the log messages are written to file in the _application/log_ directory.
The log file is the name of your environment with the _.log_ extension.

Other log listeners can be implemented and registered through the dependencies.

First, create your listener:

    namespace foo/log/listener;

    use zibo\library\log\listener\LogListener;
    use zibo\library\log\LogMessage;

    class FooLogListener implements LogListener {
        
        public function logMessage(LogMessage $message) {
            // your implementation
        }
        
    }
    
Then you add it to the core logger using the the dependencies:

    <dependency interface="zibo\library\log\listener\LogListener" class="foo\log\listener\FooLogListener" id="foo" />

    <dependency interface="zibo\library\log\Log" extends="core" id="core">
        <call method="addLogListener">
            <argument name="listener" type="dependency">
                <property name="interface" value="zibo\library\log\listener\LogListener" />
                <property name="id" value="foo" />
            </argument>
        </call>
    </dependency> 
    
You can use the [zibo\library\log\listener\AbstractLogListener](/api/class/zibo/library/log/listener/AbstractLogListener) class as a base for your listener.    