The routing is used to translate a requested path to the controller of the MVC library.

## A Route

A route defines a request path with a action in a controller. 
The controller being a PHP class, the action being a accessible method in that class.

The definition of a route provides 2 ways for passing arguments to the action:
 
* A placeholder in the path with the name of the variable for a dynamic value
* A static value in the definition of the path 

You can optionally set an id to a route to retrieve it in your code. 
By using ids, you are able to override a path through your configuration without changing your code.

Keep your code clean by implementing only one action in a action method. 
Limiting a route to a specific, or multiple request methods (GET, POST, ...) can help you with this. 

## Routes.xml

You can define routes through XML in a file called _routes.xml_. 
This file goes into the config directory of the package directory structure. 

An example of a routes.xml file:

    <?xml version="1.0" encoding="UTF-8"?>
    <routes>
        <route path="/foo" controller="vendor\controller\FooController" />    
        <route path="/bar/%id%/image" controller="vendor\controller\BarController" action="imageAction" id="bar.image" methods="get,head" />    
    </routes>
    
### Minimal Route

The minimal definition of a route consists of a path and a controller. 
When no action is defined, the method _indexAction_ is assumed.

    <route path="/foo" controller="vendor\controller\FooController" />

### Route Per Request Method

You can define a different action for a different request method.

    <route path="/blog" controller="vendor\controller\BlogController" action="indexAction" methods="get,head" />    
    <route path="/blog" controller="vendor\controller\BlogController" action="addAction" methods="post" />    

When the path _/blog_ is requested with a GET or HEAD method, it will be translated into _BlogController->indexAction()_. 
The same request with a POST method will be translated into _BlogController->addAction()_.
    
### Dynamic Route

A dynamic route can be used to match everything relative to the defined path.

    <route path="/web" controller="zibo\core\mvc\controller\WebController" dynamic="true" />
    
When the path _/web/directory/file_ is requested, it will be translated into _WebController->indexAction('directory', 'file')_.
    
### Route With A Request Argument

Request arguments can be defined between _%_. 
The name of the variable in the action method signature should be used.

    <route path="/bar/%id%/image" controller="vendor\controller\BarController" action="imageAction" id="bar.image" methods="get,head" />    

### Route With A Predefined Argument

You can predefine arguments for the action without needing them in your request path:

    <route path="/todo" controller="zibo\wiki\controller\WikiController" action="pageAction" id="todo">
        <argument name="page" type="scalar">
            <property name="value" value="Todo" />
        </argument>
    </route>
    
This route will go to the Todo page of the wiki. 

Predefined arguments are defined the same way as an argument for a dependency call.
See the dependencies page for a more detailed explaination.

## Obtain A URL

In PHP, you can obtain the full URL for defined routes from Zibo. 
URL's are requested with a route id.
The defined path parameters of the route can be filled in when requesting a URL.

Assume the following route definition:

    <?xml version="1.0" encoding="UTF-8"?>
    <routes>
        <route path="/foo" controller="vendor\controller\FooController" id="foo" />    
        <route path="/bar/%id%/image" controller="vendor\controller\BarController" id="bar.image" />    
        <route path="/web" controller="zibo\core\mvc\controller\WebController" id="web" dynamic="true" />
    </routes>
 
In PHP you can generate the URLs with the following code:
 
     $urlFoo = $zibo->getUrl('foo'); 
     $urlBarImage = $zibo->getUrl('bar.image', array('id' => 5));
     
     // $urlFoo = 'http://www.example.com/foo'
     // $urlBarImage = 'http://www.example.com/bar/5/image'
     
For dynamic routes, you just add your parameters as path variables, order does matter:

     $urlWeb = $zibo->getUrl('web');
     $urlWeb .= '/param1/param2';
     
     // $urlWeb = 'http://www.example.com/web/param1/param2'
     
This web URL will be dispatched as:

    zibo\core\mvc\controller\WebController->indexAction('param1', 'param2');