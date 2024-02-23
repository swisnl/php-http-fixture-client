<?php

namespace Swis\Http\Fixture;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\String\UnicodeString;

class ResponseBuilder implements ResponseBuilderInterface
{
    /**
     * @var string
     */
    private const TYPE_BODY = 'mock';

    /**
     * @var string
     */
    private const TYPE_HEADERS = 'headers';

    /**
     * @var string
     */
    private const TYPE_STATUS = 'status';

    /**
     * @var string
     */
    private $fixturesPath;

    /**
     * @var \Psr\Http\Message\ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var \Psr\Http\Message\StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @var array
     */
    private $domainAliases = [];

    /**
     * @var array
     */
    private $ignoredQueryParameters = [];

    /**
     * @var bool
     */
    private $strictMode = false;

    /**
     * @param string                                          $fixturesPath
     * @param \Psr\Http\Message\ResponseFactoryInterface|null $responseFactory
     * @param \Psr\Http\Message\StreamFactoryInterface|null   $streamFactory
     */
    public function __construct(
        string $fixturesPath,
        ?ResponseFactoryInterface $responseFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ) {
        $this->fixturesPath = $fixturesPath;
        $this->responseFactory = $responseFactory ?: Psr17FactoryDiscovery::findResponseFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * @return array
     */
    public function getDomainAliases(): array
    {
        return $this->domainAliases;
    }

    /**
     * @param array $domainAliases
     *
     * @return $this
     */
    public function setDomainAliases(array $domainAliases): self
    {
        $this->domainAliases = $domainAliases;

        return $this;
    }

    /**
     * @return array
     */
    public function getIgnoredQueryParameters(): array
    {
        return $this->ignoredQueryParameters;
    }

    /**
     * @param array $ignoredQueryParameters
     *
     * @return $this
     */
    public function setIgnoredQueryParameters(array $ignoredQueryParameters): self
    {
        $this->ignoredQueryParameters = $ignoredQueryParameters;

        return $this;
    }

    /**
     * @return bool
     */
    public function useStrictMode(): bool
    {
        return $this->strictMode;
    }

    /**
     * @param bool $strictMode
     *
     * @return $this
     */
    public function setStrictMode(bool $strictMode): self
    {
        $this->strictMode = $strictMode;

        return $this;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @throws \RuntimeException
     * @throws \Swis\Http\Fixture\MockNotFoundException
     *
     * @return ResponseInterface
     */
    public function build(RequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory
            ->createResponse($this->getMockStatusForRequest($request))
            ->withBody($this->getMockBodyForRequest($request));

        foreach ($this->getMockHeadersForRequest($request) as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @throws \RuntimeException
     *
     * @return int
     */
    protected function getMockStatusForRequest(RequestInterface $request): int
    {
        try {
            $file = $this->getMockFilePathForRequest($request, self::TYPE_STATUS);

            return (int) file_get_contents($file);
        } catch (MockNotFoundException $e) {
            return 200;
        }
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    protected function getMockHeadersForRequest(RequestInterface $request): array
    {
        try {
            $file = $this->getMockFilePathForRequest($request, self::TYPE_HEADERS);

            return json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (MockNotFoundException $e) {
            return [];
        }
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @throws \RuntimeException
     * @throws \Swis\Http\Fixture\MockNotFoundException
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function getMockBodyForRequest(RequestInterface $request): StreamInterface
    {
        $file = $this->getMockFilePathForRequest($request, self::TYPE_BODY);

        return $this->streamFactory->createStreamFromFile($file);
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param string                             $type
     *
     * @throws \Swis\Http\Fixture\MockNotFoundException
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function getMockFilePathForRequest(RequestInterface $request, string $type): string
    {
        $possiblePaths = $this->getPossibleMockFilePathsForRequest($request, $type);

        $file = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $file = $path;
                break;
            }
        }

        if (null === $file) {
            throw new MockNotFoundException('No fixture file found. Check possiblePaths for files that can be used.', $possiblePaths);
        }

        if (strpos(realpath($file), realpath($this->getFixturesPath())) !== 0) {
            throw new \RuntimeException(sprintf('Path to file "%s" is out of bounds.', $file));
        }

        return $file;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param string                             $type
     *
     * @return array
     */
    protected function getPossibleMockFilePathsForRequest(RequestInterface $request, string $type): array
    {
        $fixturesPath = $this->getFixturesPath();
        $host = $this->getHostFromRequest($request);
        $path = $this->getPathFromRequest($request);
        $method = $this->getMethodFromRequest($request);
        $query = $this->getQueryFromRequest($request);

        $basePathToFile = implode('/', [$fixturesPath, $host, $path]);

        $possibleFiles = [];

        if ('' !== $query) {
            $possibleFiles[] = implode('.', [$basePathToFile, $query, $method, $type]);
            $possibleFiles[] = implode('.', [$basePathToFile, $query, $type]);
        }

        $possibleFiles[] = implode('.', [$basePathToFile, $method, $type]);
        $possibleFiles[] = implode('.', [$basePathToFile, $type]);

        if ($this->useStrictMode()) {
            $possibleFiles = array_slice($possibleFiles, 0, 1);
        }

        return $possibleFiles;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return string
     */
    protected function getHostFromRequest(RequestInterface $request): string
    {
        $host = trim($request->getUri()->getHost(), '/');

        $domainAliases = $this->getDomainAliases();
        if (array_key_exists($host, $domainAliases)) {
            return $domainAliases[$host];
        }

        return $host;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return string
     */
    protected function getPathFromRequest(RequestInterface $request): string
    {
        return trim($request->getUri()->getPath(), '/');
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return string
     */
    protected function getMethodFromRequest(RequestInterface $request): string
    {
        return strtolower($request->getMethod());
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param string                             $replacement
     *
     * @return string
     */
    protected function getQueryFromRequest(RequestInterface $request, string $replacement = '-'): string
    {
        $query = urldecode($request->getUri()->getQuery());
        $parts = array_filter(
            explode('&', $query),
            function (string $part) {
                return !$this->isQueryPartIgnored($part);
            }
        );
        sort($parts);
        $query = implode('&', $parts);

        return (new UnicodeString(str_replace(['\\', '/', '?', ':', '*', '"', '>', '<', '|'], $replacement, $query)))
            ->folded()
            ->ascii()
            ->replaceMatches('/[-_\s]+/', $replacement)
            ->trim($replacement)
            ->toString();
    }

    /**
     * @param string $part
     *
     * @return bool
     */
    protected function isQueryPartIgnored(string $part): bool
    {
        foreach ($this->getIgnoredQueryParameters() as $parameter) {
            if ($part === $parameter || strncmp($part, $parameter.'=', strlen($parameter) + 1) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    protected function getFixturesPath(): string
    {
        return rtrim($this->fixturesPath, '/');
    }
}
