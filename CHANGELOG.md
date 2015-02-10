# CHANGELOG

To get the diff for a specific change, go to https://github.com/EllisV/oxid-console/commit/XXX where XXX is the change hash
To get the diff between two versions, go to https://github.com/EllisV/oxid-console/compare/v1.1.3...v1.1.4

* 1.1.5 (2015-02-10)
    * Change LICENSE to MIT and modify file headers

* 1.1.4 (2014-10-27)
    * (af63d2c) Deprecate console:update command

* 1.1.3 (2014-10-22)
    * (be06157) Modify source code to new OXID standards
    * (cff5a53) Bugfix for migration filename pattern not working with numbers

* 1.1.2 (2014-06-19)

    * (de92637) Add recursive flag on mkdir in createMissingFolders() method of generate module command
    * feature #7 (1b7fa80) Generate translation files with module scaffold
    * (6b984f2) Update version number to 0.0.1-DEV on module scaffold
    * (ed9a9ac) Removed unnecessary check for output interface on migration command
    * bug fix #5 and feature #9 (9306e0e) Implement oxNullOutput for debug ignoring

* 1.1.1 (2014-05-06)

    * feature #4 (7aab7b6) _tableExists() method in oxMigrationQuery
    * bug fix #3 (b224139) No more PHP Warning if no modules are active

* 1.1.0 (2014-04-15)

    * (9b923fe) Initial Update Manager
    * (fe5d867) Catch exceptions on getLatestVersion() in Update Manager
    * (f3f46d6) Clearing cache command able to delete directories too

* 1.0.1 (2014-04-14)

    * feature #2 (77d001f) Add oxMigrationQuery::_columnExists()
    * (1f5d892) Modify PHP file headers licence info
    * documentation #1 (e8b20b0) Add an example of migration query

