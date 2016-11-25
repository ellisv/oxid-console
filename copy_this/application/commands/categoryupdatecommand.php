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
 * @author        OXID Professional services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */

/**
 * Category update command
 *
 * Updates category tree of all shops and prints the result.
 */
class CategoryUpdateCommand extends oxConsoleCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('cat:update');
        $this->setDescription('Updates category tree');
    }

    /**
     * {@inheritdoc}
     */
    public function help(oxIOutput $oOutput)
    {
        $oOutput->writeLn('Usage: cat:update');
        $oOutput->writeLn();
        $oOutput->writeLn('This command updates category tree of all shops');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(oxIOutput $oOutput)
    {
        /** @var oxCategoryList $oCategoryList */
        $oCategoryList = oxNew('oxCategoryList');

        $oOutput->writeLn('Running category update');
        $oCategoryList->updateCategoryTree();

        foreach ($oCategoryList->getUpdateInfo() as $sInfo) {
            $oOutput->writeLn(strip_tags($sInfo));
        }

        $oOutput->writeLn('Category update finished successfully');
    }
}
