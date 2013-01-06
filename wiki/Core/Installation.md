For a development installation you need to have 3 directories:

* __public__  
The public directory is your web document root.  
* __modules__  
Your modules of Zibo are placed in this directory
* __application__  
The files specific to your application come in this directory

## With Composer

You can use [Composer](http://getcomposer.org) for a automatic installation. 
 
After installing Composer, create a _composer.json_ file in your installation directory with the following contents:

    {
        "minimum-stability": "dev",
        "config": {
            "bin-dir": "application",
            "vendor-dir": "application/vendor"
        },    
        "require": {
            "zibo/core": "*",
        },
        "repositories": [
            {
                "type": "composer",
                "url": "http://www.zibo.be"
            }
        ]
    }

Now you can simply run: 

    composer install

## Manual Installation

### Preparation

To keep everything together, we first gonna create a installation directory.
This will be our container for all the files of the installation.
You can choose the name and location of this directory.

    mkdir <installation-directory>
    cd <installation-directory>

After being changed into the installation directory, we are ready for the actual installation.

Create the directories for _public_, _modules_ and _application_:  
    
    mkdir public
    mkdir modules
    mkdir application

### The Core
    
Copy the _zibo.core_ directory to your _modules_ directory:

    cp -r <zibo.core directory> modules
   
Copy the main _index.php_ file to/in your _public_ directory:

    cp modules/zibo.core/src/index.php public
        
### The Console
        
You can setup the console by copying it to your application directory:

    cp modules/zibo.core/src/console.php application/
    
### The Configuration

Create a bootstrap configuration file by copying the default one.
    
    cp modules/zibo.core/src/config.php application/bootstrap.config.php
    
Edit the bootstrap configuration file to match your actual configuration.
The file is documented and should be self-explainatory.

    nano application/bootstrap.config.php
    
Edit the _console.php_ and the _index.php_ script and adjust the following line:

    const ZIBO_CONFIG = 'bootstrap.config.php';
    
_Note: it's best to use absolute paths, you can then access the console.php from anywhere._

### The Webserver

Make sure the application directory is writable for the user of the webserver. 
For Apache, this is usually done with the command:

    chown www-data:www-data application

You Zibo installation is now ready, the only thing left to do is make your _public_ directory available in your webserver.

This can be done by linking the public directory into the document root of your webserver.
Another way is actually configuring the webserver so it knows of your installation.

Unfortunatly this is outside the scope of this document.
You should check the documentation of your webserver for this. 