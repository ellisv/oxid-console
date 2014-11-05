<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EllisV\Oxid\Console\Bootstrapper;

use Symfony\Component\Filesystem\Filesystem;

class Resolver implements ResolverInterface
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
     * Return directory of where OXID is stored.
     *
     * Checks if .oxidconsolerc exists and if it contains directory value
     * Checks if composer.json exists and if it contains extra->oxid-web-dir
     * Checks if config.inc.php and bootstrap.php is present
     *
     * @param string $directory
     *
     * @return string Directory where OXID is stored
     */
    public function resolve($directory)
    {
        // TODO: Implement resolve() method

        return $directory;
    }
}
