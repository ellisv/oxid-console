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
 * Generate migration console command
 */
class GenerateMigrationCommand extends oxConsoleCommand
{

    /**
     * Configure current command
     *
     * Usage:
     *   $this->setName( 'my:command' )
     *   $this->setDescription( 'Executes my command' );
     */
    public function configure()
    {
        $this->setName('g:migration');
        $this->setDescription('Generate new migration file');
    }

    /**
     * Output help text of command
     *
     * @param oxIOutput $oOutput
     */
    public function help(oxIOutput $oOutput)
    {
        $oOutput->writeLn('Usage: g:migration <word> [<second_word>...]');
        $oOutput->writeLn();
        $oOutput->writeLn('Generates blank migration class.');
        $oOutput->writeLn('Migration name depends on words you have written.');
        $oOutput->writeLn();
        $oOutput->writeLn('If no words were passed you will be asked to input them');
    }

    /**
     * Execute current command
     *
     * @param oxIOutput $oOutput
     */
    public function execute(oxIOutput $oOutput)
    {
        $sMigrationsDir = OX_BASE_PATH . 'migration' . DIRECTORY_SEPARATOR;
        if (!is_dir($sMigrationsDir)) {
            mkdir($sMigrationsDir);
        }

        $sTemplatePath = $this->_getTemplatePath();

        $sMigrationName = $this->_parseMigrationNameFromInput();
        if (!$sMigrationName) {
            do {
                $sMigrationName = $this->_askForMigrationNameInput();
            } while (!$sMigrationName);
        }

        $sMigrationFileName = oxMigrationQuery::getCurrentTimestamp() . '_' . strtolower($sMigrationName) . '.php';
        $sMigrationFilePath = $sMigrationsDir . $sMigrationFileName;

        /** @var Smarty $oSmarty */
        $oSmarty = oxRegistry::get('oxUtilsView')->getSmarty();
        $oSmarty->assign('sMigrationName', $sMigrationName);
        $sContent = $oSmarty->fetch($sTemplatePath);

        file_put_contents($sMigrationFilePath, $sContent);

        if (!file_exists($sMigrationsDir . '.htaccess')) {
            $sContent = <<<EOL
# disabling file access
<FilesMatch .*>
order allow,deny
deny from all
</FilesMatch>

Options -Indexes
EOL;

            file_put_contents($sMigrationsDir . '.htaccess', $sContent);
        }

        $oOutput->writeLn("Sucessfully generated $sMigrationFileName");
    }

    /**
     * Get template path
     *
     * This allows us to override where template file is stored
     *
     * @return string
     */
    protected function _getTemplatePath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'migration.tpl';
    }

    /**
     * Ask for migration tokens input
     *
     * @return array
     */
    protected function _askForMigrationNameInput()
    {
        $oInput = $this->getInput();
        $aTokens = explode(' ', $oInput->prompt('Enter short description'));

        return $this->_buildMigrationName($aTokens);
    }

    /**
     * Parse migration name from input arguments
     *
     * @return string
     */
    protected function _parseMigrationNameFromInput()
    {
        $oInput = $this->getInput();

        $aTokens = $oInput->getArguments();
        array_shift($aTokens); // strip out command name

        return $this->_buildMigrationName($aTokens);
    }

    /**
     * Build migration name from tokens
     *
     * @param array $aTokens
     *
     * @return string
     */
    protected function _buildMigrationName(array $aTokens)
    {
        $sMigrationName = '';

        foreach ($aTokens as $sToken) {

            if (!$sToken) {
                continue;
            }

            $sMigrationName .= ucfirst($sToken);
        }

        return $sMigrationName;
    }
}
