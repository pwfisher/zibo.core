The basic configuration of Zibo is achieved through a set of key-value pairs. 
A key has at least 2 parts. The parts of a key are separated with a _._ (dot). 

The parameters are read from the _config_ directory in the module directory structure.  

## Storage

Zibo uses ini files to store the parameter. 
The first part of the parameter key defines the filename. 
When you save the parameter _mail.recipient.john_, your value will be saved in _application/config/mail.ini_.

When you retrieve the parameter _mail.recipient.john_, Zibo will look for _config/mail.ini_ in the directory structure. 
The lookup is from bottom up, from system, through modules, up to application. 
This way, a key in application will override the same key in modules or system and as such provide a overrideable system.

Assume the following configuration:

    mail.recipient.john = john@gmail.com
    mail.recipient.mark = mark@gmail.com
    mail.recipient.sarah = sarah@gmail.com
    mail.sender = no-reply@gmail.com
    system.memory = 8M

This is stored in 2 files:

_mail.ini_

    recipient.john = john@gmail.com
    recipient.mark = mark@gmail.com
    recipient.sarah = sarah@gmail.com
    sender = no-reply@gmail.com

and _system.ini_

    memory = 8M

_mail.ini_ can also be rewritten like: 

    sender = no-reply@gmail.com

    [recipient]
    john = john@gmail.com
    mark = mark@gmail.com
    sarah = sarah@gmail.com

You can store parameters which are environment specific by creating a subdirectory in your _config_ directory with the environments name:

* application/config/dev/system.ini
* application/config/prod/system.ini

Both files can contain a different memory limit for each environment.

## Get A Parameter

Some examples, assume the following configuration:

    mail.recipient.john = john@gmail.com
    mail.recipient.mark = mark@gmail.com
    mail.recipient.sarah = sarah@gmail.com
    mail.sender = no-reply@gmail.com
    system.memory = 8M

In PHP, you can retrieve a value with the following code:

    $value1 = $zibo->getParameter('system.memory');
    $value2 = $zibo->getParameter('unexistant.configuration.key');
    
    // $value1 = '8M'
    // $value2 = null

You can pass a default value. 
When the parameter is not set, the provided default value will be returned.

    $default = 'Default value';
    $value = $zibo->getParameter('unexistant.configuration.key', $default);
    
    // $value = 'Default value';

The parameters can also act as a configuration tree. 
You can get an array with all the defined recipients:

    $recipients = $zibo->getParameter('mail.recipient');
    
    // $recipients = array(
    //     'john' => 'john@gmail.com',
    //     'mark' => 'mark@gmail.com',
    //     'sarah' => 'sarah@gmail.com'
    // )

    $mail = $zibo->getParameter('mail');
    
    // $mail = array(
    //     'sender' => 'no-reply@gmail.com',
    //     'recipients' => array(
    //         'john' => 'john@gmail.com',
    //         'mark' => 'mark@gmail.com',
    //         'sarah' => 'sarah@gmail.com',
    //     ),
    // )

You can flatten fetched hierarchy if needed

    use zibo\library\config\Config
    ...
    $mail = $zibo->getParameter('mail');
    $mail = Config::flattenConfig($mail);
    
    // $mail = array(
    //     'sender' => 'no-reply@gmail.com',
    //     'recipients.john' => 'john@gmail.com',
    //     'recipients.mark' => 'mark@gmail.com',
    //     'recipients.sarah' => 'sarah@gmail.com',
    // )

## Set A Parameter

Assume the following configuration:

    mail.recipient = john@gmail.com
    system.memory = 8M

And the following PHP code:

    $recipients = array(
        'john' => 'john@gmail.com',
        'mark' => 'mark@gmail.com',
        'sarah' => 'sarah@gmail.com',
    );
    
    $zibo->setParameter('mail.recipient', $recipients);
    $zibo->setParameter('system.memory', '16M');

This code will set the configuration to the following:

    mail.recipient.john = john@gmail.com
    mail.recipient.mark = mark@gmail.com
    mail.recipient.sarah = sarah@gmail.com
    system.memory = 16M
        
## Variables    
    
There are 4 variables available: 

* __%application%__   
The path of the application directory
* __%environment%__  
The name of the running environment
* __%path%__  
The path of the module, it can be used to create correct paths for files in your module.
* __%public%__  
The path of the public directory

Assume the file _/var/www/zibo/modules/foo/config/bar.ini_ with content:
    
    data.counties = %path%/data/countries.txt

Will be translated in the following parameter:
    
    bar.data.countries = /var/www/zibo/modules/foo/data/countries.txt
