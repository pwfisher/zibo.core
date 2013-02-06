The console gives you a quick tool to perform some maintenance tasks on your installation.

## Use The Console

To run a single command of the console, use:

    php console.php [<command>]
    
When no command is provided, the help will be displayed.
    
You can run the console as a interactive shell:

    php console.php --shell
    
The interactive shell has tab auto completion and a input history. 

## Register A Command

### Through Dependencies

The easiest way to register a command is to define a dependency of your command:

    <?xml version="1.0" encoding="UTF-8"?>
    <container>  
        <dependency interface="zibo\core\console\command\Command" class="HelloCommand" />
    </container> 

### Through An Event

The _console_ event gets triggered when the console is being initialized.
Register a event listener in your _zibo\core\module\Module_ implementation and register your command(s) when the event gets triggered.

    use zibo\core\console\Console;
    use zibo\core\module\Module;
    use zibo\core\Zibo;

    class HelloModule implements Module {

        public function boot(Zibo $zibo) {
            $zibo->registerEventListener(Console::EVENT_CONSOLE, array($this, 'registerConsoleCommands'));
        }

        public function registerConsoleCommands(Zibo $zibo, Console $console) {
            $interpreter = $console->getInterpreter();
            $interpreter->registerCommand(new HelloCommand());
        }

    }

## Create A Command

You can create your own commands for the console.

The following sample command takes a name as optional argument and prints it out:

    use zibo\core\console\command\AbstractCommand;
    use zibo\core\console\output\Output;
    use zibo\core\console\InputValue;

    class HelloCommand extends AbstractCommand {
    
        public function __construct() {
            parent::__construct('hello', 'Say a greeting');
            
            $this->addArgument('name', 'Your name', true);
        }
        
        public function execute(InputValue $input, Output $output) {
            $name = $input->getArgument('name', 'John Doe');

            $output->write('Hello ' . $name);
        }
    
    }
    
### Add Autocompletion To Your Command

The interactive console has tab autocompletion builtin.

For your command however, the autocompletion depends on the data it handles.
The console cannot know this and you will have to implement it manually.
It's optional but can improve the user experience of your command. 

To add the autocompletion to your command, you simply implement the AutoCompletable interface in it.

    use zibo\core\console\command\AbstractCommand;
    use zibo\core\console\AutoCompletable;
    
    ...

    class HelloCommand extends AbstractCommand implements AutoCompletable {
    
        ...
        
        /**
         * Performs auto complete on the provided input
         * @param string $input The input value to auto complete
         * @return array|null Array with the auto completion matches or null when
         * no auto completion is available
         */
        public function autoComplete($input) {
            ...
        }
    
    }
    
The parameter _$input_ of the _autoComplete_ method, is the current console prompt without the command.

For example:

    > help j
    
When the user presses tab, the _$input_ variable will be 'j'.