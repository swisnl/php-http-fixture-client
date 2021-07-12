<?php

namespace Swis\Http\Fixture\Tests;

use GuzzleHttp\Psr7\Utils;
use Http\Discovery\MessageFactoryDiscovery;
use PHPUnit\Framework\TestCase;
use Swis\Http\Fixture\MockNotFoundException;
use Swis\Http\Fixture\ResponseBuilder;
use Swis\Http\Fixture\ResponseBuilderInterface;

class ResponseBuilderTest extends TestCase
{
    /**
     * @test
     * @dataProvider getResponses
     *
     * @param string $url
     * @param string $method
     * @param string $expectedMock
     */
    public function itCanBuildAResponse(string $url, string $method, string $expectedMock)
    {
        $builder = $this->getBuilder();

        $messageFactory = MessageFactoryDiscovery::find();

        $expectedResponse = $messageFactory->createResponse(
            200,
            null,
            [],
            Utils::streamFor(file_get_contents($this->getFixturesPath().'/'.$expectedMock))
        );
        $actualResponse = $builder->build(
            $messageFactory->createRequest($method, $url)
        );

        $this->assertEquals($expectedResponse->getBody()->__toString(), $actualResponse->getBody()->__toString());
    }

    public function getResponses(): array
    {
        return [
            // Simple
            ['http://example.com/api/articles', 'GET', 'example.com/api/articles.mock'],
            // Nested
            ['http://example.com/api/articles/1', 'GET', 'example.com/api/articles/1.mock'],
            // With simple query
            ['http://example.com/api/comments?query=json', 'GET', 'example.com/api/comments.query=json.mock'],
            // With complex query
            ['http://example.com/api/comments?query=json&foo[]=bar&foo[]=baz', 'GET', 'example.com/api/comments.foo[]=bar&foo[]=baz&query=json.mock'],
            // With query fallback
            ['http://example.com/api/comments?foo=bar', 'GET', 'example.com/api/comments.mock'],
            // With method
            ['http://example.com/api/people', 'GET', 'example.com/api/people.get.mock'],
            // With method fallback
            ['http://example.com/api/people', 'POST', 'example.com/api/people.mock'],
            // With query and method
            ['http://example.com/api/tags?query=json', 'POST', 'example.com/api/tags.query=json.post.mock'],
            // With query and method fallback
            ['http://example.com/api/tags?foo=bar', 'GET', 'example.com/api/tags.mock'],
        ];
    }

    /**
     * @test
     */
    public function itCanBeSetToStrictMode()
    {
        $builder = $this->getBuilder();

        // Strict mode off
        $this->assertFalse($builder->useStrictMode());

        $messageFactory = MessageFactoryDiscovery::find();
        $builder->build($messageFactory->createRequest('POST', 'http://example.com/api/articles?foo=bar'));

        // Strict mode on
        $builder->setStrictMode(true);
        $this->assertTrue($builder->useStrictMode());

        $this->expectException(MockNotFoundException::class);

        $messageFactory = MessageFactoryDiscovery::find();
        $builder->build($messageFactory->createRequest('POST', 'http://example.com/api/articles?foo=bar'));
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenItCantFindAFixture()
    {
        $this->expectException(MockNotFoundException::class);

        $messageFactory = MessageFactoryDiscovery::find();
        $this->getBuilder()->build($messageFactory->createRequest('GET', 'http://example.com/api/lorem-ipsum'));
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenPathIsOutOfBounds()
    {
        $this->expectException(\RuntimeException::class);

        $messageFactory = MessageFactoryDiscovery::find();
        $this->getBuilder()->build($messageFactory->createRequest('GET', 'http://example.com/../../out-of-bounds'));
    }

    /**
     * @test
     */
    public function itCanBuildAResponseUsingDomainAliases()
    {
        $messageFactory = MessageFactoryDiscovery::find();

        $expectedResponse = $messageFactory->createResponse(
            200,
            '',
            [],
            Utils::streamFor(file_get_contents($this->getFixturesPath().'/example.com/api/articles.mock'))
        );

        $actualResponse = $this->getBuilder()->build($messageFactory->createRequest('GET', 'http://foo.bar/api/articles'));

        $this->assertEquals($expectedResponse->getBody()->__toString(), $actualResponse->getBody()->__toString());
    }

    /**
     * @test
     */
    public function itCanBuildAResponseWithCustomHeaders()
    {
        $messageFactory = MessageFactoryDiscovery::find();

        $expectedResponse = $messageFactory->createResponse(
            200,
            '',
            ['X-Made-With' => 'PHPUnit'],
            Utils::streamFor(file_get_contents($this->getFixturesPath().'/example.com/api/articles.mock'))
        );

        $actualResponse = $this->getBuilder()->build($messageFactory->createRequest('GET', 'http://example.com/api/articles'));

        $this->assertEquals($expectedResponse->getHeaders(), $actualResponse->getHeaders());
    }

    /**
     * @test
     */
    public function itCanBuildAResponseWithCustomStatus()
    {
        $messageFactory = MessageFactoryDiscovery::find();

        $expectedResponse = $messageFactory->createResponse(
            500,
            '',
            [],
            Utils::streamFor(file_get_contents($this->getFixturesPath().'/example.com/api/articles.mock'))
        );
        $actualResponse = $this->getBuilder()->build($messageFactory->createRequest('GET', 'http://example.com/api/articles'));

        $this->assertEquals($expectedResponse->getStatusCode(), $actualResponse->getStatusCode());
    }

    /**
     * @return \Swis\Http\Fixture\ResponseBuilder
     */
    protected function getBuilder(): ResponseBuilderInterface
    {
        return new ResponseBuilder($this->getFixturesPath(), ['foo.bar' => 'example.com']);
    }

    /**
     * @return string
     */
    protected function getFixturesPath(): string
    {
        return __DIR__.'/_fixtures';
    }
}
