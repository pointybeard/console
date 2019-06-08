# Console Extension for Symphony CMS

-   Version: 1.1.0
-   Date: June 08 2019
-   [Release notes](https://github.com/pointybeard/console/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/console)

A [Symphony CMS](http://getsymphony.com) extension that provides access to the Symphony core from the command-line.

Developers can include commands in their extensions, allowing for operations not suited to a web frontend. The command API gives straightforward access to the Symphony core framework, including database, config, authentication, and logs.

## Installation

This is an extension for [Symphony CMS](http://getsymphony.com). Add it to the `/extensions` folder of your Symphony CMS installation, then enable it though the interface.

### Requirements

This extension requires PHP7.2 or later.

### Optional Setup

To simply accessing Symphony commands on the command line, it is recommend to do the following:

1.  Make the `bin/symphony` script executable with `chmod +x extensions/console/bin/symphony`
2.  Add `extensions/console/bin/symphony` to your `PATH` or create a symbolic link in a location that resides in the `PATH` e.g. `/usr/local/sbin`. This will allow you to call the Symphony command from anywhere.

## Usage

From the console, you can run the following command

    php -f /path/to/extensions/console/bin/symphony -- [args]

For help information, use `--help` or `-h`. E.G.

    php -f /path/to/extensions/console/bin/symphony -- --help

or, if you followed the "Optional Setup" above, just

    symphony --help

**This remainder of this document assumes you have set up the extension using the "Optional Setup" steps above.**

### Getting Started

The Console extension looks for commands in the `bin/` folder of extensions you have installed, and also in `workspace/bin/`. You can see a list of commands by running `symphony` without any arguments. A list like this will be displayed:

    Below is a list of all available commands. (use --usage for details on
    executing individual commands):

       console/hello
       console/token

At any time you can use `--help` or `-h` to get help. If you have also specified a command (see below), you will get help for that particular command instead.

Use the `EXTENSION` and `COMMAND` arguments to run a particular command. This extension comes with two commands out of the box: `hello` and `token`.

To run the `hello` command use the following:

    bin/symphony console hello --nope

You should see output like this:

    Hello! Here are the arguments & options available

    ARGUMENTS
     1: extension => console
     2: command => hello

    OPTIONS & FLAGS
     -t, --token => true

    UNRECOGNISED
    nope => true

### Authentication

Some commands may require you are authenticated before you use them. To do this, either provide the name of the user you want to authenticate as with `-u <username>` or the auth token of that user with `-t <token>`. When using `-u`, you will be prompted to enter your password.

## Writing a custom command

To write a command, create a class that extends `Symphony\Console\AbstractCommand` and place it into `workspace/bin/`. Alternatively, put it into the `bin/` folder of any Extension.

Any command you write must have a namespace starting with `Symphony\Console\Commands\` followed by the name of your extension (e.g. `namespace Symphony\Console\Commands\MyExtension`) or `workspace` (i.e. `namespace Symphony\Console\Commands\Workspace`).

Here is an example of a very basic Command called `test` placed in `workspace/bin/`:

```php
<?php
namespace Symphony\Console\Commands\Workspace;

use Symphony\Console as Console;
use pointybeard\Helpers\Cli;

class Test extends Console\AbstractCommand
{
    public function __construct() {
        parent::__construct(
            "1.0.0", // Version number
            "a really simple test command", // Description of this command
            "symphony workspace test" //Optional example of how to use this command
        );
    }

    public function execute(Console\Interfaces\InputHandlerInterface $input) : bool
    {
        (new Cli\Message\Message)
            ->message("Greetings. This is the test command!")
            ->display()
        ;

        return true;
    }
}
```

From within the `execute()` method, you have full access to the Symphony core framework. For example, to get the database object, use `\Symphony::Database()`. Anything you would normally do in an extension, you can do here (e.g. triggering delegates, accessing sections or fields).

### Requiring Authentication

You can secure your commands so that anyone using it must provide valid Symphony author credentials. To do this, instead of extending `Symphony\Console\AbstractCommand`, extend `Symphony\Console\Lib\AuthenticatedCommand`. When you command is run, Console will notice and force the user to provide a authentication with `-u` or `-t`.

When extending `AuthenticatedCommand`, you must provide an `authenticate()` method in your command. The simplest way is to use the `hasCommandRequiresAuthenticateTrait` trait. It includes a boilerplate `authenticate()` method and generally is more than adequate. It will check if the user is logged in and throw an `AuthenticationFailedException` if not.

Here is the same 'test' command from above, but this time it requires authentication:

```php
<?php
namespace Symphony\Console\Commands\Workspace;

use Symphony\Console as Console;
use pointybeard\Helpers\Cli;

class Test extends Console\AbstractCommand implements Console\Interfaces\AuthenticatedCommandInterface
{
    use Console\Traits\hasCommandRequiresAuthenticateTrait;

    public function __construct() {
        parent::__construct(
            "1.0.0", // Version number
            "a really simple test command", // Description of this command
            "symphony workspace test" //Optional example of how to use this command
        );
    }

    public function execute(Console\Interfaces\InputHandlerInterface $input) : bool
    {
        (new Cli\Message\Message)
            ->message("Greetings. This is the test command!")
            ->display()
        ;

        return true;
    }
}
```

## Multiple Symphony installations on the same Host

Note that if you follow the "Optional Steps" above, running `symphony` will always be in the context of that one particular installation.

If you run multiple sites across multiple installations of Symphony, remember that the Console extension will work with only the installation of Symphony it itself was installed and enabled on.

A solution is to place the Console extension folder outside of the Symphony CMS install, symlink the it into each `extensions/` folder per install of Symphony, and provide the path to Symphony at run-time with `$SYMPHONY_DOCROOT`.

E.g.

One install of Symphony is called banana and another called apple. The same console extension folder, which is in `~/source` is symlink'd accordingly into the `extensions` folder.
```
    ## ln -s ~/source/console /var/www/symphony-banana/extensions/
    SYMPHONY_DOCROOT=/var/www/symphony-banana symphony

    ## ln -s ~/source/console /var/www/symphony-apple/extensions/
    SYMPHONY_DOCROOT=/var/www/symphony-apple symphony
```

Using `SYMPHONY_DOCROOT` like this gives the Console extension context and will load up the correct install of Symphony at run-time.

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/console/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/console/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"Console Extension for Symphony CMS" is released under the [MIT License](http://www.opensource.org/licenses/MIT).

## Credits

*   Some inspiration taken from the [Symfony Console Component](https://github.com/symfony/console) (although no code has been used).
