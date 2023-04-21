<?php

namespace Swis\Http\Fixture\Tests;

use GuzzleHttp\Psr7\Utils;
use Http\Discovery\Psr17FactoryDiscovery;
use PHPUnit\Framework\TestCase;
use Swis\Http\Fixture\MockNotFoundException;
use Swis\Http\Fixture\ResponseBuilder;
use Swis\Http\Fixture\ResponseBuilderInterface;

class ResponseBuilderTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider getResponses
     *
     * @param string $url
     * @param string $method
     * @param string $expectedMock
     */
    public function itCanBuildAResponse(string $url, string $method, string $expectedMock): void
    {
        // arrange
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $responseFactory = Psr17FactoryDiscovery::findResponseFactory();

        $expectedResponse = $responseFactory->createResponse()
            ->withBody(Utils::streamFor(file_get_contents($this->getFixturesPath().'/'.$expectedMock)));

        // act
        $actualResponse = $this->getBuilder()->build($requestFactory->createRequest($method, $url));

        // assert
        $this->assertEquals($expectedResponse->getBody()->__toString(), $actualResponse->getBody()->__toString());
    }

    public function getResponses(): array
    {
        return [
            // Simple
            ['https://example.com/api/articles', 'GET', 'example.com/api/articles.mock'],
            // Nested
            ['https://example.com/api/articles/1', 'GET', 'example.com/api/articles/1.mock'],
            // With simple query
            ['https://example.com/api/comments?query=json', 'GET', 'example.com/api/comments.query=json.mock'],
            // With complex query
            ['https://example.com/api/comments?query=json&foo[]=bar&foo[]=baz', 'GET', 'example.com/api/comments.foo[]=bar&foo[]=baz&query=json.mock'],
            // With query fallback
            ['https://example.com/api/comments?foo=bar', 'GET', 'example.com/api/comments.mock'],
            // With method
            ['https://example.com/api/people', 'GET', 'example.com/api/people.get.mock'],
            // With method fallback
            ['https://example.com/api/people', 'POST', 'example.com/api/people.mock'],
            // With query and method
            ['https://example.com/api/tags?query=json', 'POST', 'example.com/api/tags.query=json.post.mock'],
            // With query and method fallback
            ['https://example.com/api/tags?foo=bar', 'GET', 'example.com/api/tags.mock'],
        ];
    }

    /**
     * @test
     */
    public function itCanBeSetToStrictMode(): void
    {
        $builder = $this->getBuilder();
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();

        // Strict mode off
        $this->assertFalse($builder->useStrictMode());
        $builder->build($requestFactory->createRequest('POST', 'https://example.com/api/articles?foo=bar'));

        // Strict mode on
        $builder->setStrictMode(true);
        $this->assertTrue($builder->useStrictMode());
        $this->expectException(MockNotFoundException::class);
        $builder->build($requestFactory->createRequest('POST', 'https://example.com/api/articles?foo=bar'));
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenItCantFindAFixture(): void
    {
        // arrange
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();

        // assert
        $this->expectException(MockNotFoundException::class);

        // act
        $this->getBuilder()->build($requestFactory->createRequest('GET', 'https://example.com/api/lorem-ipsum'));
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenPathIsOutOfBounds(): void
    {
        // arrange
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();

        // assert
        $this->expectException(\RuntimeException::class);

        // act
        $this->getBuilder()->build($requestFactory->createRequest('GET', 'https://example.com/../../out-of-bounds'));
    }

    /**
     * @test
     */
    public function itCanBuildAResponseUsingDomainAliases(): void
    {
        // arrange
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $responseFactory = Psr17FactoryDiscovery::findResponseFactory();
        $builder = $this->getBuilder()->setDomainAliases(['foo.bar' => 'example.com']);

        $expectedResponse = $responseFactory->createResponse()
            ->withBody(Utils::streamFor(file_get_contents($this->getFixturesPath().'/example.com/api/articles.mock')));

        // act
        $actualResponse = $builder->build($requestFactory->createRequest('GET', 'https://foo.bar/api/articles'));

        // assert
        $this->assertEquals($expectedResponse->getBody()->__toString(), $actualResponse->getBody()->__toString());
    }

    /**
     * @test
     */
    public function itCanBuildAResponseWithIgnoredParameters(): void
    {
        // arrange
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $responseFactory = Psr17FactoryDiscovery::findResponseFactory();
        $builder = $this->getBuilder()->setIgnoredQueryParameters(['ignore-me', 'ignore-me-too']);

        $expectedResponse = $responseFactory->createResponse()
            ->withBody(Utils::streamFor(file_get_contents($this->getFixturesPath().'/example.com/api/articles.mock')));

        // act
        $actualResponse = $builder->build($requestFactory->createRequest('GET', 'https://example.com/api/articles?ignore-me=true&ignore-me-too'));

        // assert
        $this->assertEquals($expectedResponse->getBody()->__toString(), $actualResponse->getBody()->__toString());
    }

    /**
     * @test
     */
    public function itCanBuildAResponseWithCustomHeaders(): void
    {
        // arrange
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $responseFactory = Psr17FactoryDiscovery::findResponseFactory();

        $expectedResponse = $responseFactory->createResponse()
            ->withHeader('X-Made-With', 'PHPUnit');

        // act
        $actualResponse = $this->getBuilder()->build($requestFactory->createRequest('GET', 'https://example.com/api/articles'));

        // assert
        $this->assertEquals($expectedResponse->getHeaders(), $actualResponse->getHeaders());
    }

    /**
     * @test
     */
    public function itCanBuildAResponseWithCustomStatus(): void
    {
        // arrange
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $responseFactory = Psr17FactoryDiscovery::findResponseFactory();

        $expectedResponse = $responseFactory->createResponse(500);

        // act
        $actualResponse = $this->getBuilder()->build($requestFactory->createRequest('GET', 'https://example.com/api/articles'));

        // assert
        $this->assertEquals($expectedResponse->getStatusCode(), $actualResponse->getStatusCode());
    }

    /**
     * @return \Swis\Http\Fixture\ResponseBuilder
     */
    protected function getBuilder(): ResponseBuilderInterface
    {
        return new ResponseBuilder($this->getFixturesPath());
    }

    /**
     * @return string
     */
    protected function getFixturesPath(): string
    {
        return __DIR__.'/_fixtures';
    }
}
