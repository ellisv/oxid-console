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
 * Cache Clear command
 *
 * Clears out OXID cache from tmp folder
 */
class CacheClearCommand extends oxConsoleCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('cache:clear');
        $this->setDescription('Clear OXID cache from tmp folder');
    }

    /**
     * {@inheritdoc}
     */
    public function help(oxIOutput $oOutput)
    {
        $oOutput->writeLn('Usage: cache:clear [options]');
        $oOutput->writeLn();
        $oOutput->writeLn('This command clears out contents of OXID tmp folder');
        $oOutput->writeLn('It applies following rules:');
        $oOutput->writeLn(' * Does not delete .htaccess');
        $oOutput->writeLn(' * Does not delete smarty directory but its contents by default');
        $oOutput->writeln();
        $oOutput->writeLn('Available options:');
        $oOutput->writeLn('  -s, --smarty     Clears out only smarty cache');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(oxIOutput $oOutput)
    {
        $oInput = $this->getInput();
        $sTmpDir = $this->_appendDirectorySeparator(oxRegistry::getConfig()->getConfigParam('sCompileDir'));
        if (!is_dir($sTmpDir)) {
            $oOutput->writeLn('Seems that compile directory does not exist');
        }

        $oOutput->writeLn('Clearing OXID cache...');

        $this->_clearDirectory($sTmpDir . 'smarty');
        if (!$oInput->hasOption(array('s', 'smarty'))) {
            // If there are no options for clearing smarty cache only
            $this->_clearDirectory($sTmpDir, array('.htaccess', 'smarty'));
        }

        $oOutput->writeLn('Cache cleared successfully');
    }

    /**
     * Clear files in given directory, except those which
     * are in $aKeep array
     *
     * @param string $sDir
     * @param array $aKeep
     */
    protected function _clearDirectory($sDir, $aKeep = array())
    {
        $sDir = $this->_appendDirectorySeparator($sDir);

        foreach (glob($sDir . '*') as $sFilePath) {
            $sFileName = basename($sFilePath);
            if (in_array($sFileName, $aKeep)) {
                continue;
            }

            is_dir($sFilePath)
                ? $this->_removeDirectory($sFilePath)
                : unlink($sFilePath);
        }
    }

    /**
     * Remove directory
     *
     * @param string $sPath
     */
    protected function _removeDirectory($sPath)
    {
        if (!is_dir($sPath)) {
            return;
        }

        $oIterator = new RecursiveDirectoryIterator($sPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $oFiles = new RecursiveIteratorIterator($oIterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($oFiles as $oFile) {
            if ($oFile->getFilename() == '.' || $oFile->getFilename() === '..') {
                continue;
            }

            $oFile->isDir()
                ? rmdir($oFile->getRealPath())
                : unlink($oFile->getRealPath());
        }

        rmdir($sPath);
    }

    /**
     * Append directory separator to path
     *
     * @param string $sPath
     *
     * @return string
     */
    protected function _appendDirectorySeparator($sPath)
    {
        if (substr($sPath, -1) != DIRECTORY_SEPARATOR) {
            return $sPath . DIRECTORY_SEPARATOR;
        }

        return $sPath;
    }
}
