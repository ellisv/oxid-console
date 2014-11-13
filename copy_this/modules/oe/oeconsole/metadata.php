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
 * Metadata version
 */
$sMetadataVersion = '1.1';
 
/**
 * Module information
 */
$aModule = array(
    'id'           => 'oeconsole',
    'title'        => 'OXID-Console (CLI Interface for OXID eShop)',
    'description'  => 'OXID-Console module to give you a console interface to manage your shop',
    'version'      => '1.1.4',
    'author'       => 'OXID eSales AG',
    'url'          => 'http://www.oxid-esales.com',
    'email'        => 'info@oxid-esales.com',
    'extend'       => array(
    ),
    'files'        => array(
        'oxmigrationexception'  => 'oe/oeconsole/core/exception/oxmigrationexception.php',
        'oxconsoleexception' => 'oe/oeconsole/core/exception/oxconsoleexception.php',
        'oxnulloutput'  => 'oe/oeconsole/core/oxnulloutput.php',
        'oxiconsoleinput' => 'oe/oeconsole/core/interface/oxiconsoleinput.php',
        'oxioutput' => 'oe/oeconsole/core/interface/oxioutput.php',
        'oxconsoleapplication' => 'oe/oeconsole/core/oxconsoleapplication.php',
        'oxconsoleoutput' => 'oe/oeconsole/core/oxconsoleoutput.php',
        'oxmigrationhandler' => 'oe/oeconsole/core/oxmigrationhandler.php',
        'oxconsolecommand' => 'oe/oeconsole/core/oxconsolecommand.php',
        'oxmigrationquery' => 'oe/oeconsole/core/oxmigrationquery.php',
        'oxstatefixermodule' => 'oe/oeconsole/core/oxstatefixermodule.php',
        'oxargvinput' => 'oe/oeconsole/core/oxargvinput.php',
        'oxspecificshopconfig' => 'oe/oeconsole/core/oxspecificshopconfig.php',
    )
);
