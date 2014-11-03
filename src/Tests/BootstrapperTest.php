<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EllisV\Oxid\Console\Tests;

use EllisV\Oxid\Console\Bootstrapper;

class BootstrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testBootstrap()
    {
        $filesystem = $this->getMockBuilder('\Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $bootstrapper = new Bootstrapper($filesystem);
        $bootstrapper->bootstrap('');
    }
}
