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
 * Null output
 *
 * It is very useful for ignoring command output
 */
class oxNullOutput implements oxIOutput
{

    /**
     * {@inheritdoc}
     */
    public function write($sMessage)
    {
        // Doing nothing...
    }

    /**
     * {@inheritdoc}
     */
    public function writeLn($sMessage = '')
    {
        // Doing nothing...
    }
}
