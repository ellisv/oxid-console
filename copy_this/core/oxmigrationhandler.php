<?php
/**
 * This file is part of OXID Console.
 *
 * OXID Console is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID Console is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author        OXID Professional services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */

/**
 * Migration handler for migration queries
 *
 * Only one instance of this class is allowed
 *
 * Sample usage:
 *      $oMigrationHandler = oxMigrationHandler::getInstance()
 *      $oMigrationHandler->run( '2014030709325468' );
 */
class oxMigrationHandler
{

    /**
     * @var bool Object already created?
     */
    protected static $_oCreated = false;

    /**
     * @var string Full path of cache file
     */
    protected $_sCacheFilePath;

    /**
     * @var string Directory where migration paths are stored
     */
    protected $_sMigrationQueriesDir;

    /**
     * @var array Executed queries
     */
    protected $_aExecutedQueryNames = array();

    /**
     * @var oxMigrationQuery[]
     */
    protected $_aQueries = array();

    /**
     * @var oxDb
     */
    protected $_oDb;

    /**
     * Constructor.
     *
     * Loads migration queries cache and builds migration queries objects
     */
    public function __construct()
    {
        if (static::$_oCreated) {
            /** @var oxMigrationException $oEx */
            $oEx = oxNew('oxMigrationException');
            $oEx->setMessage('Only one instance for oxMigrationHandler allowed');
            throw $oEx;
        }

        static::$_oCreated = true;

        $createSql = "CREATE TABLE IF NOT EXISTS `oxmigrationstatus` (
						  `OXID` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
						  `version` varchar(255) NOT NULL UNIQUE,
						  `executed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores the migrationstatus';
						";

        $this->_oDB = oxDb::getDb();
        $this->_oDB->execute($createSql);

        $this->_aExecutedQueryNames = $this->_oDB->getAssoc("SELECT * FROM oxmigrationstatus");
        $this->_sMigrationQueriesDir = OX_BASE_PATH . 'migration' . DIRECTORY_SEPARATOR;

        $this->_buildMigrationQueries();
    }

    /**
     * Run migration
     *
     * @param string|null    $sTimestamp The time at which the migrations aims. Only migrations up to this point are being executed
     * @param oxIOutput|null $oOutput    Out handler for console output
     */
    public function run($sTimestamp = null, oxIOutput $oOutput = null)
    {
        if (null === $sTimestamp) {
            $sTimestamp = oxMigrationQuery::getCurrentTimestamp();
        }

        foreach ($this->getQueries() as $oQuery) {
            $oQuery->getTimestamp() < $sTimestamp
                ? $this->_goUp($oQuery, $oOutput)
                : $this->_goDown($oQuery, $oOutput);
        }
    }

    /**
     * Executes an UP Migration
     *
     * @param oxMigrationQuery $oQuery  The query object that is being executed
     * @param oxIOutput        $oOutput The output handler for the console output that might be generated
     *
     * @return bool
     */
    protected function _goUp(oxMigrationQuery $oQuery, oxIOutput $oOutput = null)
    {
        if ($this->isExecuted($oQuery)) {
            return false;
        }

        if ($oOutput) {
            $oOutput->writeLn(
                sprintf(
                    '[DEBUG] Migrating up %s %s',
                    $oQuery->getTimestamp(),
                    $oQuery->getClassName()
                )
            );
        }

        $oQuery->up();
        $this->setExecuted($oQuery);

        return true;
    }

    /**
     * Executes a DOWN Migration
     *
     * @param oxMigrationQuery $oQuery  The query object that is being executed
     * @param oxIOutput        $oOutput The output handler for the console output that might be generated
     *
     * @return bool
     */
    protected function _goDown(oxMigrationQuery $oQuery, oxIOutput $oOutput = null)
    {
        if (!$this->isExecuted($oQuery)) {
            return false;
        }

        if ($oOutput) {
            $oOutput->writeLn(
                sprintf(
                    '[DEBUG] Migrating down %s %s',
                    $oQuery->getTimestamp(),
                    $oQuery->getClassName()
                )
            );
        }

        $oQuery->down();
        $this->setUnexecuted($oQuery);

        return true;
    }

    /**
     * Is query already executed?
     *
     * @param oxMigrationQuery $oQuery The query object that is being checked for
     *
     * @return bool
     */
    public function isExecuted(oxMigrationQuery $oQuery)
    {

        foreach ($this->_aExecutedQueryNames as $executedQuery) {
            if ($oQuery->getFilename() == $executedQuery[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set query as executed
     *
     * @param oxMigrationQuery $oQuery The query object that is being set to executed
     */
    public function setExecuted(oxMigrationQuery $oQuery)
    {

        $sSQL = sprintf("REPLACE INTO oxmigrationstatus SET version = '%s'", $oQuery->getFilename());
        $this->_oDB->execute($sSQL);
    }

    /**
     * Set query as not executed
     *
     * @param oxMigrationQuery $oQuery The query object that is being set to not executed
     */
    public function setUnexecuted(oxMigrationQuery $oQuery)
    {
        $sSQL = sprintf("DELETE FROM oxmigrationstatus WHERE version = '%s'", $oQuery->getFilename());
        $this->_oDB->execute($sSQL);
    }

    /**
     * Load and build migration files
     *
     * @throws oxMigrationException
     *
     * @return bool
     */
    protected function _buildMigrationQueries()
    {
        if (!is_dir($this->_sMigrationQueriesDir)) {
            return false;
        }

        $oDirectory = new RecursiveDirectoryIterator($this->_sMigrationQueriesDir);
        $oFlattened = new RecursiveIteratorIterator($oDirectory);

        $aFiles = new RegexIterator($oFlattened, oxMigrationQuery::REGEXP_FILE);
        foreach ($aFiles as $sFilePath) {
            include_once $sFilePath;

            $sClassName = $this->_getClassNameFromFilePath($sFilePath);

            /** @var oxMigrationQuery $oQuery */
            $oQuery = oxNew($sClassName);

            $this->addQuery($oQuery);
        }

        return true;
    }

    /**
     * Get migration queries class name parsed from file path
     *
     * @param string $sFilePath The path of the file to extract the class name from
     *
     * @throws oxMigrationException
     * @return string Class name in lower case most cases
     */
    protected function _getClassNameFromFilePath($sFilePath)
    {
        $sFileName = basename($sFilePath);
        $aMatches = array();

        if (!preg_match(oxMigrationQuery::REGEXP_FILE, $sFileName, $aMatches)) {
            /** @var oxMigrationException $oEx */
            $oEx = oxNew('oxMigrationException');
            $oEx->setMessage('Could not extract class name from file name');
            throw $oEx;
        }

        return $aMatches[2] . 'migration';
    }

    /**
     * Set migration queries
     *
     * @param oxMigrationQuery[] $aQueries An Array of Quries to be stored insite $this->_aQueries
     */
    public function setQueries(array $aQueries)
    {
        $this->_aQueries = $aQueries;
    }

    /**
     * Get migration queries
     *
     * @return oxMigrationQuery[]
     */
    public function getQueries()
    {
        ksort($this->_aQueries);

        return $this->_aQueries;
    }

    /**
     * Add query
     *
     * @param oxMigrationQuery $oQuery The query to be added
     */
    public function addQuery(oxMigrationQuery $oQuery)
    {
        $this->_aQueries[$oQuery->getTimestamp()] = $oQuery;
    }

    /**
     * Set executed queries
     *
     * @param array $aExecutedQueryNames An array of queries, which should be set to executed
     */
    public function setExecutedQueryNames(array $aExecutedQueryNames)
    {
        $this->_aExecutedQueryNames = $aExecutedQueryNames;
    }

    /**
     * Get executed queries
     *
     * @return array
     */
    public function getExecutedQueryNames()
    {
        return $this->_aExecutedQueryNames;
    }
}
