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
 * Console output
 */
class oxConsoleOutput implements oxIOutput
{
    /**
     * @var resource
     */
    protected $_oStream;

    /**
     * Constructor
     *
     * Opens up output stream
     *
     * @author Fabien Potencier <fabien@symfony.com>
     * @link   https://github.com/symfony/Console/blob/v2.6.0/Output/ConsoleOutput.php#L44
     */
    public function __construct()
    {
        $sStream = 'php://stdout';
        if (!$this->_hasStdoutSupport()) {
            $sStream = 'php://output';
        }

        $this->_oStream = fopen($sStream, 'w');
    }

    /**
     * {@inheritdoc}
     */
    public function write($sMessage)
    {
        if (!@fwrite($this->_oStream, $sMessage)) {
            /** @var oxConsoleException $oEx */
            $oEx = oxNew('oxConsoleException');
            $oEx->setMessage('Could not write to output');
            throw $oEx;
        }

        fflush($this->_oStream);
    }

    /**
     * {@inheritdoc}
     */
    public function writeLn($sMessage = '')
    {
        $this->write($sMessage . PHP_EOL);
    }

    /**
     * Get stream
     *
     * @return resource
     */
    public function getStream()
    {
        return $this->_oStream;
    }

    /**
     * Returns true if current environment supports writing console output to
     * STDOUT.
     *
     * IBM iSeries (OS400) exhibits character-encoding issues when writing to
     * STDOUT and doesn't properly convert ASCII to EBCDIC, resulting in garbage
     * output.
     *
     * @author Fabien Potencier <fabien@symfony.com>
     * @link   https://github.com/symfony/Console/blob/v2.6.0/Output/ConsoleOutput.php#L109
     *
     * @return boolean
     */
    protected function _hasStdoutSupport()
    {
        return ('OS400' != php_uname('s'));
    }
}
