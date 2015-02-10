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
 * Migration query class. All migration queries must extend this class
 *
 * Migration class filename must match timestamp_classname.php format
 */
abstract class oxMigrationQuery
{

    /**
     * Regexp used for regexp timestamp validation
     */
    const REGEXP_TIMESTAMP = '/^\d{14}$/';

    /**
     * Regexp used for regexp file name validation
     *
     * First match: timestamp
     * Second match: class name without "migration" appended
     */
    const REGEXP_FILE = '/(\d{14})_([a-zA-Z][a-zA-Z0-9]+)\.php$/';

    /**
     * @var string Timestamp
     */
    protected $_sTimestamp;

    /**
     * @var string Migration query file name
     */
    protected $_sFilename;

    /**
     * @var string Class name in lower case
     */
    protected $_sClassName;

    /**
     * Constructor
     *
     * Extracts timestamp from filename of migration query
     */
    public function __construct()
    {
        $oReflection = new ReflectionClass($this);
        $sFilename = basename($oReflection->getFileName());
        $aMatches = array();

        if (!preg_match(static::REGEXP_FILE, $sFilename, $aMatches)) {
            /** @var oxMigrationException $oEx */
            $oEx = oxNew('oxMigrationException');
            $oEx->setMessage('Wrong migration query file name');
            throw $oEx;
        }

        $this->setFilename($sFilename);
        $this->setTimestamp($aMatches[1]);
        $this->setClassName($aMatches[2] . 'migration');

        $this->_validateClassName();
    }

    /**
     * Validates class name
     *
     * @throws oxMigrationException
     */
    protected function _validateClassName()
    {
        if (strtolower(get_class($this)) != $this->getClassName()) {
            /** @var oxMigrationException $oEx */
            $oEx = oxNew('oxMigrationException');
            $oEx->setMessage('Wrong migration class naming convention. Maybe you forgot to append "Migration"?');
            throw $oEx;
        }
    }

    /**
     * Migrate up
     */
    abstract public function up();

    /**
     * Migrate down
     */
    abstract public function down();

    /**
     * Set timestamp
     *
     * @param string $sTimestamp
     *
     * @throws oxMigrationException When wrong timestamp format passed
     */
    public function setTimestamp($sTimestamp)
    {
        if (!static::isValidTimestamp($sTimestamp)) {
            /** @var oxMigrationException $oEx */
            $oEx = oxNew('oxMigrationException');
            $oEx->setMessage('Wrong timestamp format passed');
            throw $oEx;
        }

        $this->_sTimestamp = $sTimestamp;
    }

    /**
     * Get timestamp
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->_sTimestamp;
    }

    /**
     * Set filename
     *
     * @param string $sFilename
     */
    public function setFilename($sFilename)
    {
        $this->_sFilename = $sFilename;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->_sFilename;
    }

    /**
     * Set class name
     *
     * @param string $sClassName
     */
    public function setClassName($sClassName)
    {
        $this->_sClassName = strtolower($sClassName);
    }

    /**
     * Get class name
     *
     * @return string in lower case
     */
    public function getClassName()
    {
        return $this->_sClassName;
    }

    /**
     * Is valid timestamp for migration query
     *
     * @param $sTimestamp
     *
     * @return int
     */
    public static function isValidTimestamp($sTimestamp)
    {
        return preg_match(static::REGEXP_TIMESTAMP, $sTimestamp);
    }

    /**
     * Get current timestamp
     *
     * @return string
     */
    public static function getCurrentTimestamp()
    {
        return date('YmdHis');
    }

    /**
     * Table exists in database?
     *
     * @param string $sTable Table name
     *
     * @return bool
     */
    protected static function _tableExists($sTable)
    {
        $sQuery = "
            SELECT 1
            FROM information_schema.tables
            WHERE table_name = ?
        ";

        return (bool)oxDb::getDb()->getOne($sQuery, array($sTable));
    }

    /**
     * Column exists in specific table?
     *
     * @param string $sTable Table name
     * @param string $sColumn Column name
     *
     * @return bool
     */
    protected static function _columnExists($sTable, $sColumn)
    {
        $oConfig = oxRegistry::getConfig();
        $sDbName = $oConfig->getConfigParam('dbName');
        $sSql = 'SELECT 1
                    FROM information_schema.COLUMNS
                    WHERE
                        TABLE_SCHEMA = ?
                    AND TABLE_NAME = ?
                    AND COLUMN_NAME = ?';

        $oDb = oxDb::getDb();

        return (bool)$oDb->getOne($sSql, array($sDbName, $sTable, $sColumn));
    }
}
