# OXID Console

OXID Console is php console application for OXID Shop. It provides an API for writting various commands.

By default there are following commands included:

* `cache:clear` - Clear OXID cache from tmp folder
* `console:update` - Update your console application
* `db:update` - Updates database views
* `fix:states` - Fixes modules metadata states
* `g:migration` - Generate new migration file
* `g:module` - Generate new module scaffold
* `list` - *(default)* List of all available commands
* `migrate` - Run migration scripts
* `module:list` - Outputs module list table

This OXID Console repository has **Migration Handler**, **Module State Fixer** and module_enabled_count() included.

## Getting started

The entry point of console application is `php oxid`. It will execute default command which is `list`. To call a specific command run `php oxid [command]`. If you need help about specific command run `php oxid [command] -h` or `php oxid [command] --help`

## Defining your own command

* Commands get autoloaded from `application/commands/` and `[module_path]/commands/` directories. But you can always add or remove commands with `add()` or `remove()` methods of console application
* You can access console application `$this->getConsoleApplication()` and input object `$this->getInput()` in your command class
* Command filename must follow `[your_command]command.php` format
* Class name must be the same as filename, e.g. CacheClearCommand.php
* Class must extend oxConsoleCommand abstract class
* You must set name of your command on configure() method

### Template for your command:

```php
<?php

/**
 * My own command
 *
 * Demo command for learning
 */
class MyOwnCommand extends oxConsoleCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('my:own');
        $this->setDescription('Demo command for learning');
    }

    /**
     * {@inheritdoc}
     */
    public function help(oxIOutput $oOutput)
    {
        $oOutput->writeLn('This is my help output');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(oxIOutput $oOutput)
    {
        $oInput = $this->getInput();
        $oInput->hasOption(array('demo', 'd')) {
            $oOutput->writeLn('You typed in --demo or -d also');
        }

        $oOutput->writeLn('My demo command finished');
    }
}
```

## Working with arguments and options

First of all You must know that `-abc` is the same as `-a -b -c` and it is a good practice to have long version of option too, e.g. `-a` is the same as `--all`.
Console Input provides you with various methods to work with options. There is nothing better than a good example:

### Command class

```php
<?php

/**
 * My own command
 *
 * Demo command for learning
 */
class MyOwnCommand extends oxConsoleCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('my:own');
        $this->setDescription('Demo command for learning');
    }

    /**
     * {@inheritdoc}
     */
    public function help(oxIOutput $oOutput)
    {
        // TODO: Implement help() method
    }

    /**
     * {@inheritdoc}
     */
    public function execute(oxIOutput $oOutput)
    {
        $oInput = $this->getInput();

        var_dump($oInput->hasOption(array('a', 'all')));
        var_dump($oInput->hasOption('b'));
        var_dump($oInput->hasOption('not-valid'));

        var_dump($oInput->getOption('word'));

        var_dump($oInput->getOptions());
        var_dump($oInput->getArguments());

        var_dump($oInput->getArgument(2));
    }
}
```

### php oxid my:own -o cat --all tree -bzg house --word=nice dog

```
bool(true)
bool(true)
bool(false)

string(4) "nice"

array(6) {
  'o'    => bool(true)
  'all'  => bool(true)
  'b'    => bool(true)
  'z'    => bool(true)
  'g'    => bool(true)
  'word' => string(4) "nice"
}
array(5) {
  [0] => string(6) "my:own"
  [1] => string(3) "cat"
  [2] => string(4) "tree"
  [3] => string(5) "house"
  [4] => string(3) "dog"
}

string(4) "tree"
```

## Migrations

OXID Console project includes migration handling. Lets generate sample migration by running `php oxid g:migration add amount field to demo module`.

Console application generated `migration/20140312161434_addamountfieldtodemomodule.php` file with its contents:

```php
<?php

class AddAmountFieldToDemoModuleMigration extends oxMigrationQuery
{

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        // TODO: Implement up() method.
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // TODO: Implement down() method.
    }
}
```

Migration handler can run migrations with your given timestamp *(if no timestamp provided than it assumes timestamp as current timestamp)*. Inside it saves *(in generated `cache/migrations.cache` file)* which migration queries were executed and knows which migration queries go up or go down.

Once we generated this file we run `php oxid migrate`

```
Running migration scripts
[DEBUG] Migrating up 20140312161434 addamountfieldtodemomodulemigration
Migration finished successfully
```

Now lets run the same command a second time

```
Running migration scripts
Migration finished successfully
```

*Note: No migration scripts were ran*

Ok, now lets run migrations with given timestamp of the past with `php oxid migrate 2013-01-01` command

```
Running migration scripts
[DEBUG] Migrating down 20140312161434 addamountfieldtodemomodulemigration
Migration finished successfully
```

It ran our migration query down because on given timestamp we should not have had executed that migration query.

### Example

Here is a quick example of migration query which adds a column to oxuser table

```php
<?php
// FILE: 20140414085723_adddemoculumntooxuser.php

class AddDemoCulumnToOxUserMigration extends oxMigrationQuery
{

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->_columnExists('oxuser', 'OXDEMO')) {
            return;
        }

        $sSql = "ALTER TABLE  `oxuser`
                 ADD  `OXDEMO`
                    CHAR( 32 )
                    CHARACTER SET utf8
                    COLLATE utf8_general_ci
                    NULL
                    DEFAULT NULL
                    COMMENT  'Demo field for migration'";

        oxDb::getDb()->execute($sSql);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        if (!$this->_columnExists('oxuser', 'OXDEMO')) {
            return;
        }

        oxDb::getDb()->execute('ALTER TABLE `oxuser` DROP `OXDEMO`');
    }
}
```

### Migration Query Law

* Filename must follow `YYYYMMDDHHiiss_description.php` format
* Must extend oxMigrationQuery abstract class
* Class name must be the same as description with *Migration* word appended to the end of the name

*Note: It is better to use generator for migration queries creation*

## Module state fixer

### Current problem

When you change information of module in metadata you need to reactivate the module for changes to take effect. It is a bad idea for live systems because you might loose data and it bugs out developers to do this all the time by hand.

### Solution

oxStateFixerModule which is extension of oxModule has method `fix()` which will call methods:

* fixVersion
* fixExtend
* fixFiles
* fixTemplates
* fixBlocks
* fixSettings
* fixEvents

Those methods can be executed individually because they are public.

We have provided you with `fix:states` command to work with oxStateFixerModule. Type in `php oxid fix:states --help` for more information.

## module_enabled_count()

When you want do deactivate module it is sometimes good to clean after your module, e.g. dropping tables or columns created by it. But we can not do this because we do not know how many (sub)shops are using this module, dropping tables or columns should be done only when module gets deactivated on last shop using this.

We provided you with `module_enabled_count()` function to get number of shops where your given module is activated.

## Credits

This project was inspired by [Symfony/Console](https://github.com/symfony/Console) component.
