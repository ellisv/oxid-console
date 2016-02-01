<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * Constructor.
     *
     * Loads migration queries cache and builds migration queries objects
     */
    public function __construct()
    {
        if (static::$_oCreated) {
            throw new oxMigrationException('Only one instance for oxMigrationHandler allowed');
        }

        static::$_oCreated = true;

        $createSql = '
            CREATE TABLE IF NOT EXISTS `oxmigrationstatus` (
                `OXID` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `version` varchar(255) NOT NULL UNIQUE,
                `executed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'Stores the migrationstatus\';
        ';

        $oDb = oxDb::getDb();
        $oDb->execute($createSql);

        $this->_aExecutedQueryNames = $oDb->getAssoc('SELECT * FROM oxmigrationstatus');
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

        $sSQL = 'REPLACE INTO oxmigrationstatus SET version = ?';
        oxDb::getDb()->execute($sSQL, array($oQuery->getFilename()));
    }

    /**
     * Set query as not executed
     *
     * @param oxMigrationQuery $oQuery The query object that is being set to not executed
     */
    public function setUnexecuted(oxMigrationQuery $oQuery)
    {
        $sSQL = 'DELETE FROM oxmigrationstatus WHERE version = ?';
        oxDb::getDb()->execute($sSQL, array($oQuery->getFilename()));
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
            $oQuery = new $sClassName();

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
            throw new oxMigrationException('Could not extract class name from file name');
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
