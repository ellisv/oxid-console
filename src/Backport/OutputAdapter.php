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

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Output Adapter for legacy OXID Console output interface.
 *
 * This is being used to support commands written for 1.2 and earlier releases.
 * See more at CommandAdapter.
 */
class OutputAdapter implements \oxIOutput
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Forward write.
     *
     * @param string $message
     */
    public function write($message)
    {
        $this->output->write($message);
    }

    /**
     * Perform write with new line appended.
     *
     * @param string $message
     */
    public function writeLn($message = '')
    {
        $this->write($message . PHP_EOL);
    }
}
