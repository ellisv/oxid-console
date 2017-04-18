<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Tomas Kvietkauskas <tomas@kvietkauskas.lt>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class GenerateCommandCommand
 */
class GenerateCommandCommand extends oxConsoleCommand
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
        $this->setName('g:command');
        $this->setDescription('Generate new console command file');
    }

    /**
     * Output help text of command
     *
     * @param oxIOutput $oOutput
     */
    public function help(oxIOutput $oOutput)
    {
        $oOutput->writeLn('Usage: g:command <vendor> ><word> [<second_word>...]');
        $oOutput->writeLn();
        $oOutput->writeLn('Generates blank console command class.');
        $oOutput->writeLn('Command name depends on words you have written.');
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
        $sCommandDir = OX_BASE_PATH . 'application' . DIRECTORY_SEPARATOR . 'commands' . DIRECTORY_SEPARATOR;
        $sTemplateFile = $this->_getTemplateFile();

        $sCommandName = $this->_parseCommandNameFromInput($oOutput);
        if (!$sCommandName) {
            do {
                $sCommandName = $this->_askForCommandNameInput($oOutput);
            } while (!$sCommandName);
        }

        $sCommandName .= 'Command';

        $sCommandFileName = strtolower($sCommandName) . '.php';
        $sCommandFileName = $sCommandDir . $sCommandFileName;

        /** @var Smarty $oSmarty */
        $oSmarty = oxRegistry::get('oxUtilsView')->getSmarty();
        $oSmarty->assign('sCommandName', $sCommandName);
        $sContent = $oSmarty->fetch($sTemplateFile);

        file_put_contents($sCommandFileName, $sContent);

        $oOutput->writeLn("Sucessfully generated $sCommandFileName");
    }

    /**
     * Get template file
     *
     * @return string
     */
    protected function _getTemplateFile()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'command.tpl';
    }

    /**
     * Parse command name from input arguments
     *
     * @param oxIOutput $oOutput
     * @return string
     */
    protected function _parseCommandNameFromInput(oxIOutput $oOutput)
    {
        $oInput = $this->getInput();

        $aTokens = $oInput->getArguments();
        array_shift($aTokens); // strip out command name

        return $this->_buildCommandName($aTokens, $oOutput);
    }

    /**
     * Ask for migration tokens input
     *
     * @param oxIOutput $oOutput
     * @return string
     */
    protected function _askForCommandNameInput(oxIOutput $oOutput)
    {
        $oInput = $this->getInput();
        $sVendor = trim($oInput->prompt('Enter vendor name'));
        $aTokens = explode(' ', $oInput->prompt('Enter command description'));
        array_unshift($aTokens, $sVendor);

        return $this->_buildCommandName($aTokens, $oOutput);
    }

    /**
     * Build migration name from tokens
     *
     * @param array $aTokens
     * @param oxIOutput $oOutput
     * @return string
     */
    protected function _buildCommandName(array $aTokens, oxIOutput $oOutput)
    {
        $sCommandName = '';

        foreach ($aTokens as $sToken) {

            if (!$sToken) {
                continue;
            }

            $sCommandName .= ucfirst($sToken);
        }

        if (class_exists($sCommandName . 'Command')) {
            $sCommandName = '';
            $oOutput->writeLn();
            $oOutput->writeLn($sCommandName . 'Command already exists, choose different name.');
        }

        return $sCommandName;
    }
}
