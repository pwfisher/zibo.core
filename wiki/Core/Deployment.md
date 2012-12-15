When your application is ready for production, you can use the _build_ command in the console.

    php console.php build <destination>

The _build_ command will take a destination directory as argument.
Your installation will be copied into the provided destination directory.
While doing so, your installation will be optimized for production: 

* application and module directories are flattened into a single application directory 
* configuration files are merged
* necessairy caches are enabled
* environment is set to production

The destination directory is then ready to copy to your production server.

Use the _public_ directory as your website document root.

For security reasons, place the _application_ directory outside your document root.  

The installation is now ready to use.
When you want to move the installation, you only need to edit the _config.php_ file.
Set the directories to the new paths and you are ready to go.

_Note: the build command is only available in the dev environment._