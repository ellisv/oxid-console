<?php

/*
 * This file is part of the OXID Console package.
 *
 * This file is based on Symfony\Component\Console\Output\ConsoleOutput.
 * Changes were made under copyright by Eligijus Vitkauskas for use with
 * special behaviour in OXID Console.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Console output, based on Symfony\Component\Console\Output\ConsoleOutput
 *
 * @author  Fabien Potencier <fabien@symfony.com>
 * @link    https://github.com/symfony/Console/blob/v2.6.0/Output/ConsoleOutput.php
 * @license https://github.com/symfony/Console/blob/v2.6.0/LICENSE
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
     * @author  Fabien Potencier <fabien@symfony.com>
     * @link    https://github.com/symfony/Console/blob/v2.6.0/Output/ConsoleOutput.php#L44
     * @license https://github.com/symfony/Console/blob/v2.6.0/LICENSE
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
            throw new oxConsoleException('Could not write to output');
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
     * @license https://github.com/symfony/Console/blob/v2.6.0/LICENSE
     *
     * @return boolean
     */
    protected function _hasStdoutSupport()
    {
        return ('OS400' != php_uname('s'));
    }
}
