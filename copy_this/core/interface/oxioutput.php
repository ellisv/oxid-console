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
 * Output interface implemented by any output class
 */
interface oxIOutput
{

    /**
     * Write message to an output
     *
     * @param string $sMessage
     */
    public function write($sMessage);

    /**
     * Write message to an output and append new line
     *
     * @param string $sMessage
     */
    public function writeLn($sMessage = '');
}
