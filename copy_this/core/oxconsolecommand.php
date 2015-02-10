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
 * Abstract Console Command class
 *
 * All console application commands must extend this class
 */
abstract class oxConsoleCommand
{

    /**
     * @var string Command name
     */
    protected $_sName;

    /**
     * @var string Command description
     */
    protected $_sDescription;

    /**
     * @var oxIConsoleInput
     */
    protected $_oInput;

    /**
     * @var oxConsoleApplication
     */
    protected $_oConsoleApplication;

    /**
     * Constructor
     *
     * Configures console command
     *
     * @throws oxConsoleException
     */
    public function __construct()
    {
        $this->configure();

        if (!$this->getName()) {
            /** @var oxConsoleException $oEx */
            $oEx = oxNew('oxConsoleException');
            $oEx->setMessage('Command must have a name.');
            throw $oEx;
        }
    }

    /**
     * Configure current command
     *
     * Usage:
     *   $this->setName( 'my:command' )
     *   $this->setDescription( 'Executes my command' );
     */
    abstract public function configure();

    /**
     * Output help text of command
     *
     * @param oxIOutput $oOutput
     */
    abstract public function help(oxIOutput $oOutput);

    /**
     * Execute current command
     *
     * @param oxIOutput $oOutput
     */
    abstract public function execute(oxIOutput $oOutput);

    /**
     * Set current console command name
     *
     * @param string $sName
     */
    public function setName($sName)
    {
        $this->_sName = $sName;
    }

    /**
     * Get current console command name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_sName;
    }

    /**
     * Set current console command description
     *
     * @param string $sDescription
     */
    public function setDescription($sDescription)
    {
        $this->_sDescription = $sDescription;
    }

    /**
     * Get current console command description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_sDescription;
    }

    /**
     * Set console application
     *
     * @param oxConsoleApplication $oConsoleApplication
     */
    public function setConsoleApplication(oxConsoleApplication $oConsoleApplication)
    {
        $this->_oConsoleApplication = $oConsoleApplication;
    }

    /**
     * Set input instance
     *
     * @param oxIConsoleInput $oInput
     */
    public function setInput(oxIConsoleInput $oInput)
    {
        $this->_oInput = $oInput;
    }

    /**
     * Get input instance
     *
     * @return oxIConsoleInput
     */
    public function getInput()
    {
        return $this->_oInput;
    }

    /**
     * Get console application
     *
     * @return oxConsoleApplication
     */
    public function getConsoleApplication()
    {
        return $this->_oConsoleApplication;
    }
}
