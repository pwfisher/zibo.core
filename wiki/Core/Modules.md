Everything in Zibo is a module, even the core itself. 
Modules are a great way to keep your code structured and flexible.

## Create Your Own Module

A module is a directory containing the defined [directory structure](/wiki/page/Core/Directory+Structure).
To use a module in your installation, simply place it in a modules directory as defined in your bootstrap configuration.
The module is now used by Zibo.
Depending on your enabled caches, you might have to clear your cache first before it's fully active.

_Note: Modules are only used in the dev environment, once your installation is built, everything is flattened and mixed to optimize performance._ 

## Hook Into Zibo

To hook your module into the Zibo kernel, you have to implement the [zibo\core\module\Module](/api/class/zibo/core/module/Module) interface.
This interface has a method _boot_ which is invoked before Zibo services the request and can be used to register event listeners and do other stuff.

An example of a module:

    namespace vendor\module;

    use zibo\core\module\Module;
    use zibo\core\Zibo;
    
    class MyModule implements Module {
    
        public function boot(Zibo $zibo) {
            $zibo->registerEventListener(Zibo::EVENT_PRE_RESPONSE, array($this, 'preResponse')); 
        }
        
        public function preResponse(Zibo $zibo) {
            // do stuff
        }
    
    }

Zibo will not look for your _Module_ implementation, you have to define it through the dependencies.
It's good practice to use the full module name as id of your dependency.

    <dependencies>
        <dependency interface="zibo\core\module\Module" class="vendor\module\MyModule" id="vendor.module" />
    </dependencies>