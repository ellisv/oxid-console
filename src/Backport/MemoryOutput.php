<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ellis\Oxid\Console\Backport;

/**
 * An implementation oxIOutput to write to memory so you can dump that later.
 *
 * This is being used for bridging help() of legacy oxConsoleCommand to new
 * interface.
 */
class MemoryOutput implements \oxIOutput
{
    /**
     * @var string
     */
    private $buffer = '';

    /**
     * Write a message to memory.
     *
     * @param string $message
     */
    public function write($message)
    {
        $this->buffer .= $message;
    }

    /**
     * Write a message to memory with new line appended.
     *
     * @param string $message
     */
    public function writeLn($message = '')
    {
        $this->write($message . PHP_EOL);
    }

    /**
     * Dump a buffer of memory.
     *
     * Returns a current buffer and clears it.
     *
     * @return string
     */
    public function dump()
    {
        $result = $this->buffer;
        $this->buffer = '';

        return $result;
    }
}
