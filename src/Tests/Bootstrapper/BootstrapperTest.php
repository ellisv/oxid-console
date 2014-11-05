<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EllisV\Oxid\Console\Tests\Bootstrapper;

use EllisV\Oxid\Console\Bootstrapper\Bootstrapper;

class BootstrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \EllisV\Oxid\Console\Bootstrapper\BootstrapperException
     */
    public function testBootstrap()
    {
        $filesystem = $this->getFilesystemMock();
        $filesystem->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false));

        $bootstrapper = new Bootstrapper($filesystem, $this->getResolverMock());
        $bootstrapper->bootstrap('');
    }

    private function getFilesystemMock()
    {
        return $this->getMockBuilder('\Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getResolverMock()
    {
        return $this->getMockBuilder('\EllisV\Oxid\Console\Bootstrapper\ResolverInterface')
            ->getMock();
    }
}
