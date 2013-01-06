The dependency system allows you to choose different implementations for parts of the system.
It's heavily integrated in Zibo which makes it highly flexible and customizable.

## Get A Dependency

Zibo is the facade to the dependency injector.

The most generic way to get a dependency is by providing only the interface. 
The last defined implementation of the interface will be loaded:
    
    $router = $zibo->getDependency('zibo\\library\\router\\Router');

To obtain a specific implementation, you can pass an id when retrieving a dependency:

    $input = $zibo->getDependency('zibo\\core\\console\\input\\Input', 'shell');
    
This will get the input implementation for the shell.

Loaded instances are kept in memory.
When the same dependency is requested multiple times, only a single instance is created and it will be used as result for all requests to that dependency.

## Using Dependencies As Factory

By passing construct arguments, you can let the injector act as a factory.

The injector will use the provided arguments for the constructor. 
The additional defined calls of the dependency are skipped.
The instance will not be stored in the injector.

    $validator = $zibo->getDependency('zibo\\library\\validation\\validator\\Validator', 'minmax', array('options' => array('minimum' => 5)));

## dependencies.xml

You can easily define your own dependencies in _dependencies.xml_.
This file goes into the _config_ directory of the package directory structure

The most simple definition of a dependency is a class definition.
With this definition, the class can be used in the singleton pattern or in the factory pattern.

    <?xml version="1.0" encoding="UTF-8"?>
    <container>  
        <dependency class="zibo\image\ImageUrlGenerator" />
    </container>  

To define a implementation of a interface, you can use the following dependency definition: 
    
    <dependency interface="zibo\core\console\input\Input" class="zibo\core\console\input\ReadlineInput" id="shell" />
    
_Note: The id attribute is optional but advised._

### Calls

In most cases, you will have to pass arguments to the constructors or invoke some methods before the instance is ready to use.
You can obtain this by adding calls to your definition.

A call consists of the method name and optionally some argument definitions.
You have different type of arguments.

* __null__  
Force a null value, this argument has no properties
* __scalar__  
A scalar value which can be set using _value_ as property name.
* __array__  
A array value which consists of all the set properties.
* __parameter__  
A parameter from Zibo. The _key_ property name is used to define the parameter. 
You can set a default value for the parameter by setting the _default_ property;
* __dependency__  
A dependency can again be inserted into another definition.
Set the _interface_ property to define the dependency.
You can optionally set the _id_ property to specify the instance.  
* __call__  
With this type, you can call a function, a static method or a method on a defined dependency.
You cannot define arguments for these calls.
    * Set the property _function_ with the name to invoke a function.
    * To invoke a static method, set the property _class_ with the name of the class and the property _method_ with the name of the method.
    * To invoke a method on a dependency, you can set the _interface_ property with a optional _id_ property to define the dependency. Set the _method_ property to define the method.  



When using a dependency in your argument (dependency or call), you can define the id as a Zibo parameter.
To do so, prefix and suffix your id with _%_.
    
To define the constructor of a dependency, simply add the _\_\_construct_ method to the definition.

A simple example:

    <dependency interface="zibo\core\mvc\router\RouteContainerIO" class="zibo\core\mvc\router\XmlRouteContainerIO" id="core">
        <call method="__construct">
            <argument name="environment" type="dependency">
                <property name="interface" value="zibo\core\environment\Environment" />
            </argument>
        </call>
    </dependency>

A more advanced example:

    <dependency interface="zibo\library\router\Router" class="zibo\library\router\GenericRouter" id="core">
        <call method="__construct">
            <argument name="routeContainer" type="call">
                <property name="dependency" value="zibo\core\mvc\router\RouteContainerIO" />
                <property name="method" value="getRouteContainer" />
            </argument>
        </call>
        <call method="setDefaultAction">
            <argument name="defaultController" type="parameter">
                <property name="key" value="system.default.controller" />
                <property name="default" value="zibo\library\mvc\controller\IndexController" />
            </argument>
            <argument name="defaultAction" type="parameter">
                <property name="key" value="system.default.action" />
                <property name="default" value="indexAction" />
            </argument>
        </call>
    </dependency>
    
Define the id of your dependency as a parameter:
 
    <dependency interface="zibo\library\mvc\View" class="vendor\app\view\DummyView" id="dummy">
        <call method="setObject">
            <argument name="object" type="dependency">
                <property name="interface" value="vendor\app\Object" />
                <property name="id" value="%vendor.dependency.object%" />
            </argument>
        </call>
    </dependency>
    
When the parameter is not set, null is used as id and the last defined dependency of the interface is used.