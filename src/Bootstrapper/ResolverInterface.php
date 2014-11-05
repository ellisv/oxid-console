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

/**
 * Interface Bootstrapper uses to resolve directory
 */
interface ResolverInterface
{
    /**
     * Resolve given directory and return directory
     * where OXID is stored
     *
     * @param string $directory Directory where to search
     *
     * @return string Directory where OXID is stored
     */
    public function resolve($directory);
}
