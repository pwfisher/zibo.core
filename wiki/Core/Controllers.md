Controllers are the objects which glue your models and views together.
They perform the actions of your application on the models and pack the results in a view.

Controllers are defined in the router. 
The combination of a HTTP request method and a requested path is mapped to a action in a controller.

## Simple Controller

    use zibo\app\controller\AbstractController;
    
    class FooController extends AbstractController {
    
        public function indexAction() {
            ...
        }
    
    }

## preAction And postAction

You can override the _preAction_ and the _postAction_ method if needed.
These methods take no arguments and are invoked before and after every action of your controller.
The _preAction_ method should return a boolean to state if the action should be invoked.

    use zibo\app\controller\AbstractController;
    
    class FooController extends AbstractController {

        public function preAction() {
            return true;
        }
    
        public function indexAction() {
            ...
        }
        
        public function postAction() {
            ...
        }
    
    }

## Action Arguments

The arguments of a action can be inserted in 3 ways:

* A request argument of the route
* A predefined argument of the route
* A dependency

Assume the following controller:

    <?php
    
    namespace zibo\wiki\controller;
    
    use zibo\app\controller\AbstractController;
    
    use zibo\wiki\model\Wiki;
    
    class WikiController extends AbstractController {
    
        public function pageAction(Wiki $wiki, $page) {
            ...
        }
    }

The _$page_ argument can be inserted with a request argument (#wiki.page) or a predefined argument (#help.controllers):

	<!-- config/routes.xml -->
    <?xml version="1.0" encoding="UTF-8"?>
    <routes>
        <route path="/wiki/page/%page%" controller="zibo\wiki\controller\WikiController" action="pageAction" methods="head,get" id="wiki.page" />    
        <route path="/help/controllers" controller="zibo\wiki\controller\WikiController" action="pageAction" methods="head,get" id="help.controllers">
    	    <argument name="page" type="scalar">
    		    <property name="value" value="Controllers" />
    	    </argument>
        </route>    
    </routes>
    
The _$wiki_ argument is inserted through the dependencies:

	<!-- config/dependencies.xml -->
    <?xml version="1.0" encoding="UTF-8"?>
    <container>
	    <dependency class="zibo\wiki\model\Wiki" id="wiki">
	        <call method="__construct">
	            <argument name="zibo" type="dependency">
	                <property name="interface" value="zibo\core\Zibo" />
	            </argument>
	        </call>
	    </dependency>
	</container>

## HTTP

A part of optimizing your application, is implementing the HTTP protocol as much as you can.
This will benefit the speed for your users and the load of your servers.

### Handle Not Modified

#### With A E-Tag

In your controller

    public function indexAction() {
        // gather your content
        // ...
        
        // for simplicity, your content is just $content
        $eTag = md5($content);

        // set the E-Tag to the response, a header will be created for it
        $this->response->setETag($eTag);
        
        // check the request
        if ($this->response->isNotModified($this->request)) {
            // the content hasn't changed since the last request
            $this->response->setNotModified();
            
            return;
        }
        
        // generate a view for your content and set it to the response
        // ...
    }
    
## AbstractController

The benefits of extending the AbstractController of Zibo.

### Obtain A URL

The _getUrl_ method offers you a shortcut to obtaining URL's from the router.
After the construction of your controller, you can use:

    $url = $this->getUrl('route.id');
    
You can also provide your arguments to the route:

    $url = $this->getUrl('person.detail', array('id' => 5));
    
See the [routing page](/wiki/page/Core/Routing) for more information.

### Set A Download View

Offering files for explicit download is a rather common thing to do when developing web applications.
The _setDownloadView_ method will make this an easy task for you.
It will handle all the headers needed for the download.

    use zibo\library\filesystem\File;
    
    ...
    
    // somewhere in a controller action

    $file = new File('myfile.txt');

    $this->setDownloadView($file);

You can force a name of the file for the download box of the user by providing an extra name.
You can also provide a parameter to clean up the file if it's no longer needed after the download.

    $this->setDownloadView($file, 'text.txt', true);
    
Now the download box of the user will say _text.txt_ instead of _myfile.txt_.
The _myfile.txt_ will also be deleted on the server. 
This can be useful for generated files.    