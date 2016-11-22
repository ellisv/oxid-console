<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use OxidEsales\EshopCommunity\Core\SettingsHandler;

/**
 * Module state fixer
 */
class oxModuleStateFixer extends oxModuleInstaller
{
    /**
     * Fix module states task runs version, extend, files, templates, blocks,
     * settings and events information fix tasks
     *
     * @param oxModule      $oModule
     * @param oxConfig|null $oConfig If not passed uses default base shop config
     */
    public function fix(oxModule $oModule, oxConfig $oConfig = null)
    {
        if ($oConfig !== null) {
            $this->setConfig($oConfig);
        }

        $sModuleId = $oModule->getId();

        $this->_deleteBlock($sModuleId);
        $this->_deleteTemplateFiles($sModuleId);
        $this->_deleteModuleFiles($sModuleId);
        $this->_deleteModuleEvents($sModuleId);
        $this->_deleteModuleVersions($sModuleId);

        $this->_addExtensions($oModule);

        $this->_addTemplateBlocks($oModule->getInfo("blocks"), $sModuleId);
        $this->_addModuleFiles($oModule->getInfo("files"), $sModuleId);
        $this->_addTemplateFiles($oModule->getInfo("templates"), $sModuleId);
        $settingsHandler = oxNew(SettingsHandler::class);
        $settingsHandler->setModuleType('module')->run($oModule);
        $this->_addModuleVersion($oModule->getInfo("version"), $sModuleId);
        $this->_addModuleEvents($oModule->getInfo("events"), $sModuleId);

        /** @var oxModuleCache $oModuleCache */
        $oModuleCache = oxNew('oxModuleCache', $oModule);
        $oModuleCache->resetCache();
    }
}
