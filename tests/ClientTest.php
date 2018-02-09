<?php

namespace Swis\Http\Fixture\Tests;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Swis\Http\Fixture\Client;
use Swis\Http\Fixture\ResponseBuilderInterface;

class ClientTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_send_a_request()
    {
        $request = new Request('GET', new Uri('http://example.com'));
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Swis\Http\Fixture\ResponseBuilderInterface $responseBuilder */
        $responseBuilder = $this->getMockBuilder(ResponseBuilderInterface::class)->getMock();
        $responseBuilder->expects($this->once())
            ->method('build')
            ->with($request)
            ->willReturn(new Response());

        $client = new Client($responseBuilder);
        $client->sendRequest($request);
    }
}
