## Build

When your application is ready for production, you can use the _build_ command in the console.

    php console.php build <destination>

The _build_ command will take a destination directory as argument.
Your installation will be copied into the provided destination directory.
While doing so, your installation will be optimized for best performance: 

* application and module directories are flattened into a single application directory 
* configuration files are merged
* necessairy caches are enabled
* environment is set to production unless an other environment is provided

The destination directory is then ready to deploy to your production server.

Use the _public_ directory as your website document root.

For security reasons, it's better to place the _application_ directory outside your document root.  

_Note: the build command is only available in the dev environment._

## Deploy

After your application is built, you can easily sync it to a remote server using the following command:

    php console.php deploy <profile> [<environment>] [--force]
    
_Note: By default, this command uses the ssh and rsync programs and will therefor only work on POSIX systems.
You can install the zibo.ftp module to support FTP deployment._

### Profiles

The _deploy_ command takes a profile name as argument.

A profile defines a hosting environment. 
You can define profiles through the Zibo parameters.
Assume the following _deploy.ini_ with the definition for the _staging_ profile:

    [staging]
    type = "ssh"
    environment = "prod"
    server = "server"
    ; username = "username"
    ; ssh.key = /path/to/ssh_key
    path.application = /path/to/application
    path.public = /path/to/public

The _deploy_ command will use these arguments to create a [zibo\core\deploy\DeployProfile](/api/class/zibo/core/deploy/DeployProfile) instance.
All other parameters which are set for your profile will be set as custom parameters to that DeployProfile instance.

### Events

Use the events of [zibo\core\build\Deployer](/api/class/zibo/core/deploy/Deployer) to hook extra logic into your deployment.

The following events are triggered:

* __deploy.pre__  
You can use this hook to prepare the local and/or the remote installation before the actual deploy happens.
A instance of [zibo\core\deploy\Deployer](/api/class/zibo/core/deploy/Deployer) is passed as argument to the event.

* __deploy.post__  
You can use this hook to prepare the local and/or the remote installation after the actual deploy happened.
A instance of [zibo\core\deploy\Deployer](/api/class/zibo/core/deploy/Deployer) is passed as argument to the event.
