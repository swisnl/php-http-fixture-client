<?php

namespace Swis\Http\Fixture\Tests;

use PHPUnit\Framework\TestCase;
use Swis\Http\Fixture\MockNotFoundException;

class MockNotFoundExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_get_possible_paths()
    {
        $paths = ['path1', 'path2'];
        $exception = new MockNotFoundException('message', $paths);

        $this->assertSame($paths, $exception->getPossiblePaths());
    }
}
