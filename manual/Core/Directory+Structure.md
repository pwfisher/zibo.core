## Used Directories

The following directories are used by Zibo:

* __core__   
The directory of the core module.
This needs to be set in order to bootstrap Zibo.

* __public__  
This is your actual web root. 
All files available to the public come into this directory. 

* __application__  
Everything for your application comes in this directory according to the module directory structure.
All files in application will override the files of the modules.

* __modules__
The modules directory is a optional container directory for the modules.
It can also be an array of containers.
It's used in developement environment to create a better overview or to force the order of file reads.  

Your directories should be set in _config.php_. 
See the Installation page for more details about _config.php_.

The _build_ console command will flatten your complete directory structure into a single public and application directory.
It's the most performant setup of Zibo.
See the Deployment page for more information. 

## Module Directory Structure

The directory structure for modules and the application directory looks like the following if applicable:

* __config__  
configuration resources
* __data__  
data resources needed by the implementation
* __l10n__  
translations
* __log__  
log files (only in application)
* __manual__  
sources of the manual documentation pages
* __public__  
resources for the document root of the webserver
* __src__  
sources of the implementation; the PHP files
* __test__  
a nested module directory structure
* __vendor__  
external vendor libraries
* __view__  
templates of the views