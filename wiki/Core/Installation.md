For a development installation you need to have 3 directories:

* __public__  
The public directory is your web document root.  
* __modules__  
Your modules of Zibo are placed in this directory
* __application__  
The files specific to your application come in this directory

## Installation On Linux

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

    cp -r <extract-directory>/zibo.core modules
   
Copy or link the main _index.php_ file to/in your _public_ directory:

    cp modules/zibo.core/src/index.php public
    
or

    ln -s modules/zibo.core/src/index.php public
    
_Note: when you link the main script, it's automatically updated when you update your core module._
    
### The Console
        
You can also setup the console by copying or linking it to your installation directory:

    cp modules/zibo.core/src/console.php <installation-directory>
    
or 
    
    ln -s modules/zibo.core/src/console.php <installation-directory>
    
### The Configuration

Create a bootstrap configuration file by copying the default one.
    
    cp modules/zibo.core/src/config.php config.php
    
Edit the bootstrap configuration file to match your actual configuration.
The file is documented and should be self-explainatory.

    nano config.php

### The Webserver

Make sure the application directory is writable for the user of the webserver. 
For Apache, this is usually done with the command:

    chown www-data:www-data application

You Zibo installation is now ready, the only thing left to do is make your _public_ directory available in your webserver.

This can be done by linking the public directory into the document root of your webserver.
Another way is actually configuring the webserver so it knows of your installation.

Unfortunatly this is outside the scope of this document.
You should check the documentation of your webserver for this. 