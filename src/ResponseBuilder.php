<?php

namespace Swis\Http\Fixture;

use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\ResponseFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stringy\Stringy;

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
     * @var array
     */
    private $domainAliases;

    /**
     * @var \Http\Message\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var bool
     */
    private $strictMode = false;

    /**
     * @param string                             $fixturesPath
     * @param array                              $domainAliases
     * @param \Http\Message\ResponseFactory|null $responseFactory
     */
    public function __construct(string $fixturesPath, array $domainAliases = [], ResponseFactory $responseFactory = null)
    {
        $this->fixturesPath = $fixturesPath;
        $this->domainAliases = $domainAliases;
        $this->responseFactory = $responseFactory ?: MessageFactoryDiscovery::find();
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
     * @return self
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
        return $this->responseFactory->createResponse(
            $this->getMockStatusForRequest($request),
            '',
            $this->getMockHeadersForRequest($request),
            $this->getMockBodyForRequest($request)
        );
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

            return json_decode(file_get_contents($file), true);
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
     * @return string
     */
    protected function getMockBodyForRequest(RequestInterface $request): string
    {
        $file = $this->getMockFilePathForRequest($request, self::TYPE_BODY);

        return file_get_contents($file);
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

        if (array_key_exists($host, $this->domainAliases)) {
            return $this->domainAliases[$host];
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
    protected function getQueryFromRequest(RequestInterface $request, $replacement = '-'): string
    {
        $query = urldecode($request->getUri()->getQuery());
        $parts = explode('&', $query);
        sort($parts);
        $query = implode('&', $parts);

        return (string) Stringy::create(str_replace(['\\', '/', '?', ':', '*', '"', '>', '<', '|'], $replacement, $query))
            ->toAscii()
            ->delimit($replacement)
            ->removeLeft($replacement)
            ->removeRight($replacement);
    }

    /**
     * @return string
     */
    protected function getFixturesPath(): string
    {
        return rtrim($this->fixturesPath, '/');
    }
}
