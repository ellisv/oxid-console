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
 * Specific shop config class
 *
 * Helper class for generating oxConfig instance for specific shop
 */
class oxSpecificShopConfig extends oxConfig
{

    /**
     * @var int
     */
    protected $_iShopId;

    /**
     * @var array
     */
    protected $aModuleSettings;

    /**
     * Constructor
     *
     * @param $iShopId
     */
    public function __construct($iShopId)
    {
        $this->_iShopId = $iShopId;
        $this->init();
    }

    /**
     * Returns config arrays for all shops
     *
     * @return oxSpecificShopConfig[]
     */
    public static function getAll()
    {
        $aShopIds = oxDb::getDb()->getCol('SELECT oxid FROM oxshops');
        $aConfigs = array();

        foreach ($aShopIds as $mShopId) {
            // Note: not using static::get() for avoiding checking of is shop id valid
            $aConfigs[] = new oxSpecificShopConfig($mShopId);
        }

        return $aConfigs;
    }

    /**
     * Get config object of given shop id
     *
     * @param string|integer $mShopId
     *
     * @return oxSpecificShopConfig|null
     */
    public static function get($mShopId)
    {

        $sSQL = 'SELECT 1 FROM oxshops WHERE oxid = %s';
        $oDb = oxDb::getDb();

        if (!$oDb->getOne(sprintf($sSQL, $oDb->quote($mShopId)))) { // invalid shop id
            // Not using oxConfig::_isValidShopId() because its not static, but YES it should be
            return null;
        }

        return new oxSpecificShopConfig($mShopId);
    }

    /**
     * {@inheritdoc}
     *
     * @return null|void
     */
    public function init()
    {
        // Duplicated init protection
        if ($this->_blInit) {
            return;
        }
        $this->_blInit = true;

        $this->_loadVarsFromFile();
        include getShopBasePath() . 'core/oxconfk.php';

        $this->_setDefaults();

        try {
            $sShopID = $this->getShopId();
            $blConfigLoaded = $this->_loadVarsFromDb($sShopID);

            // loading shop config
            if (empty($sShopID) || !$blConfigLoaded) {
                /** @var oxConnectionException $oEx */
                $oEx = oxNew("oxConnectionException");
                $oEx->setMessage("Unable to load shop config values from database");
                throw $oEx;
            }

            // loading theme config options
            $this->_loadVarsFromDb(
                $sShopID, null, oxConfig::OXMODULE_THEME_PREFIX . $this->getConfigParam('sTheme')
            );

            // checking if custom theme (which has defined parent theme) config options should be loaded over parent theme (#3362)
            if ($this->getConfigParam('sCustomTheme')) {
                $this->_loadVarsFromDb(
                    $sShopID, null, oxConfig::OXMODULE_THEME_PREFIX . $this->getConfigParam('sCustomTheme')
                );
            }

            // loading modules config
            $this->_loadVarsFromDb($sShopID, null, oxConfig::OXMODULE_MODULE_PREFIX);

            $aOnlyMainShopVars = array('blMallUsers', 'aSerials', 'IMD', 'IMA', 'IMS');
            $this->_loadVarsFromDb($this->getBaseShopId(), $aOnlyMainShopVars);

            $this->_loadModuleSettings($sShopID);

        } catch (oxConnectionException $oEx) {
            $oEx->debugOut();
            oxRegistry::getUtils()->showMessageAndExit($oEx->getString());
        }
    }

    /**
     * Get shop id
     *
     * @return int
     */
    public function getShopId()
    {
        return $this->_iShopId;
    }

    /**
     * Fix wrong config params for modules on key duplicates
     *
     * @param $sModuleId
     */
    public function fixWrongConfigParams($sModuleId) {

        if($aModuleSettings = $this->_getModuleSettings($sModuleId)) {
            foreach ($aModuleSettings as $sKey => $sValue) {
                if(isset($this->_aConfigParams[$sKey])) {
                    $this->_aConfigParams[$sKey] = $sValue;
                }
            }
        }

    }

    /**
     * Get setting for modules
     *
     * @param $sModuleId
     * @return array|null
     */
    private function _getModuleSettings($sModuleId) {
        if(isset($this->aModuleSettings[$sModuleId])) {
            return $this->aModuleSettings[$sModuleId];
        }
        return null;
    }

    /**
     * Load Module settings
     *
     * @param $sShopID
     */
    private function _loadModuleSettings($sShopID) {

        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);

        $sSql = sprintf(
            'SELECT REPLACE(OXMODULE, %s, "") AS OXMODULE, OXVARNAME, OXVARTYPE, %s AS OXVARVALUE FROM %s WHERE OXSHOPID = %s AND OXMODULE LIKE %s',
            $oDb->quote(oxConfig::OXMODULE_MODULE_PREFIX),
            oxRegistry::getConfig()->getDecodeValueQuery(),
            'oxconfig',
            $oDb->quote($sShopID),
            $oDb->quote(oxConfig::OXMODULE_MODULE_PREFIX . '%')
        );

        foreach($oDb->getAll($sSql) as $aModuleSetting) {
            $oModuleSettings = $aModuleSetting['OXVARVALUE'];
            if(in_array($aModuleSetting['OXVARTYPE'], array('arr','aarr'))) {
                $oModuleSettings = unserialize($oModuleSettings);
            }
            $this->aModuleSettings[$aModuleSetting['OXMODULE']][$aModuleSetting['OXVARNAME']] = $oModuleSettings;
        }

    }

}