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
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem, ResolverInterface $resolver)
    {
        $this->filesystem = $filesystem;
        $this->resolver = $resolver;
    }

    /**
     * Bootstrap OXID
     *
     * @param string $directory Directory where OXID is stored
     *
     * @throws BootstrapperException
     */
    public function bootstrap($directory)
    {
        $directory = $this->resolver->resolve($directory);

        $requiredFiles = array(
            $directory . '/bootstrap.php',
            $directory . '/config.inc.php'
        );

        if (!$this->filesystem->exists($requiredFiles)) {
            throw new BootstrapperException('OXID is not found in current working directory');
        }

        require_once $directory . '/bootstrap.php';
    }
}
