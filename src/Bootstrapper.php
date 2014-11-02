<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EllisV\Oxid\Console;

use Symfony\Component\Filesystem\Filesystem;

/**
 * OXID Bootstrapper
 *
 * @author Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 */
class Bootstrapper
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Bootstrap OXID
     *
     * @param string $directory Directory where OXID is stored
     */
    public function bootstrap($directory)
    {
        // TODO: Implement bootstrap() method

        throw new \RuntimeException('OXID is not found in current working directory');
    }
}
