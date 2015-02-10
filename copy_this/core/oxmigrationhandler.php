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
            /** @var oxMigrationException $oEx */
            $oEx = oxNew('oxMigrationException');
            $oEx->setMessage('Only one instance for oxMigrationHandler allowed');
            throw $oEx;
        }

        static::$_oCreated = true;

        $this->_sCacheFilePath = OX_BASE_PATH . 'cache' . DIRECTORY_SEPARATOR . 'migrations.cache';
        $this->_sMigrationQueriesDir = OX_BASE_PATH . 'migration' . DIRECTORY_SEPARATOR;

        if (is_file($this->_sCacheFilePath)) {
            $this->_aExecutedQueryNames = unserialize(file_get_contents($this->_sCacheFilePath));
        }

        $this->_buildMigrationQueries();
    }

    /**
     * Destructor.
     *
     * Flushes down cache to file
     */
    public function __destruct()
    {
        $hFile = fopen($this->_sCacheFilePath, 'w');
        if (!$hFile) {
            /** @var oxMigrationException $oEx */
            $oEx = oxNew('oxMigrationException');
            $oEx->setMessage('Could not open migration handler cache file');
            throw $oEx;
        }

        fwrite($hFile, serialize($this->_aExecutedQueryNames));
        fclose($hFile);
    }

    /**
     * Run migration
     *
     * @param string|null $sTimestamp
     * @param oxIOutput|null $oOutput
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
     * Migrate query up
     *
     * @param oxMigrationQuery $oQuery
     * @param oxIOutput|null $oOutput
     */
    protected function _goUp(oxMigrationQuery $oQuery, oxIOutput $oOutput = null)
    {
        if ($this->isExecuted($oQuery)) {
            return;
        }

        if ($oOutput) {
            $oOutput->writeLn(
                sprintf(
                    '[DEBUG] Migrating up %s %s',
                    $oQuery->getTimestamp(), $oQuery->getClassName()
                )
            );
        }

        $oQuery->up();
        $this->setExecuted($oQuery);
    }

    /**
     * Migrate query down
     *
     * @param oxMigrationQuery $oQuery
     * @param oxIOutput|null $oOutput
     */
    protected function _goDown(oxMigrationQuery $oQuery, oxIOutput $oOutput = null)
    {
        if (!$this->isExecuted($oQuery)) {
            return;
        }

        if ($oOutput) {
            $oOutput->writeLn(
                sprintf(
                    '[DEBUG] Migrating down %s %s',
                    $oQuery->getTimestamp(), $oQuery->getClassName()
                )
            );
        }

        $oQuery->down();
        $this->setUnexecuted($oQuery);
    }

    /**
     * Is query already executed?
     *
     * @param oxMigrationQuery $oQuery
     *
     * @return bool
     */
    public function isExecuted(oxMigrationQuery $oQuery)
    {
        return in_array($oQuery->getFilename(), $this->_aExecutedQueryNames);
    }

    /**
     * Set query as executed
     *
     * @param oxMigrationQuery $oQuery
     */
    public function setExecuted(oxMigrationQuery $oQuery)
    {
        if ($this->isExecuted($oQuery)) {
            return;
        }

        $this->_aExecutedQueryNames[] = $oQuery->getFilename();
    }

    /**
     * Set query as unexecuted
     *
     * @param oxMigrationQuery $oQuery
     */
    public function setUnexecuted(oxMigrationQuery $oQuery)
    {
        if (($mKey = array_search($oQuery->getFilename(), $this->_aExecutedQueryNames)) !== false) {
            unset($this->_aExecutedQueryNames[$mKey]);
        }
    }

    /**
     * Load and build migration files
     */
    protected function _buildMigrationQueries()
    {
        if (!is_dir($this->_sMigrationQueriesDir)) {
            return;
        }

        $oDirectory = new RecursiveDirectoryIterator($this->_sMigrationQueriesDir);
        $oFlattened = new RecursiveIteratorIterator($oDirectory);

        $aFiles = new RegexIterator($oFlattened, oxMigrationQuery::REGEXP_FILE);
        foreach ($aFiles as $sFilePath) {
            require_once $sFilePath;

            $sClassName = $this->_getClassNameFromFilePath($sFilePath);

            /** @var oxMigrationQuery $oQuery */
            $oQuery = oxNew($sClassName);

            $this->addQuery($oQuery);
        }
    }

    /**
     * Get migration queries class name parsed from file path
     *
     * @param string $sFilePath
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
     * @param oxMigrationQuery[] $aQueries
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
        return $this->_aQueries;
    }

    /**
     * Add query
     *
     * @param oxMigrationQuery $oQuery
     */
    public function addQuery(oxMigrationQuery $oQuery)
    {
        $this->_aQueries[] = $oQuery;
    }

    /**
     * Set executed queries
     *
     * @param array $aExecutedQueryNames
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
