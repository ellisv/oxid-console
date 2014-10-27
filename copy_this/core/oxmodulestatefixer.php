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
 * along with OXID Console.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    OXID Professional services
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */

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
        if ($oConfig === null) {
            $this->setConfig($oConfig);
        }

        $sModuleId = $oModule->getId();

        $this->_addExtensions($oModule);

        $this->_addTemplateBlocks($oModule->getInfo("blocks"), $sModuleId);
        $this->_addModuleFiles($oModule->getInfo("files"), $sModuleId);
        $this->_addTemplateFiles($oModule->getInfo("templates"), $sModuleId);
        $this->_addModuleSettings($oModule->getInfo("settings"), $sModuleId);
        $this->_addModuleVersion($oModule->getInfo("version"), $sModuleId);
        $this->_addModuleEvents($oModule->getInfo("events"), $sModuleId);

        /** @var oxModuleCache $oModuleCache */
        $oModuleCache = oxNew('oxModuleCache', $oModule);
        $oModuleCache->resetCache();
    }
}
