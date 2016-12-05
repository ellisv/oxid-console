UPGRADE FROM 1.1 and 1.2 to 1.3
===============================

List of files to be deleted
---------------------------

* `oxid`
* `application/commands/templates` directory
* `application/commands/cacheclearcommand.php`
* `application/commands/consoleupdatecommand.php` (only 1.1 version)
* `application/commands/databaseupdatecommand.php`
* `application/commands/fixstatescommand.php`
* `application/commands/generatemigrationcommand.php`
* `application/commands/generatemodulecommand.php`
* `application/commands/listcommand.php`
* `application/commands/migratecommand.php`
* `application/commands/modulelistcommand.php` (only 1.1 version) (or could be adjusted to a new API)
* `core/exception/oxconsoleexception.php`
* `core/exception/oxmigrationexception.php`
* `core/interface/oxiconsoleinput.php`
* `core/interface/oxioutput.php`
* `core/oxargvinput.php`
* `core/oxconsoleapplication.php`
* `core/oxconsolecommand.php`
* `core/oxconsoleoutput.php`
* `core/oxmigrationhandler.php`
* `core/oxmigrationquery.php`
* `core/oxmodulestatefixer.php` (only 1.2 version)
* `core/oxnulloutput.php`
* `core/oxspecificshopconfig.php`
* `core/oxstatefixermodule.php` (only 1.1 version)

All the core functionality is now being provided with a composer package so we
no longer need oxid console specific files in your project source.

## What if I have modified core command to suit my needs?

This can still be used be renaming a command to something different than a
core command. Command will be working but you will still get a deprecation
warning because it uses old API. Keep reading the guide to fix that.


Adjust custom commands to a new Command API
-------------------------------------------

Old OXID Console command API has been deprecated in favor of using Symfony
Console Component API. To learn more about that see:

* https://symfony.com/doc/current/console.html
* https://symfony.com/doc/current/components/console.html#learn-more
