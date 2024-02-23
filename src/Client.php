<?php

namespace Swis\Http\Fixture;

use Http\Mock\Client as MockClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client extends MockClient
{
    /**
     * @var \Swis\Http\Fixture\ResponseBuilderInterface
     */
    protected $fixtureResponseBuilder;

    /**
     * @param \Swis\Http\Fixture\ResponseBuilderInterface                                   $fixtureResponseBuilder
     * @param \Http\Message\ResponseFactory|\Psr\Http\Message\ResponseFactoryInterface|null $responseFactory
     */
    public function __construct(ResponseBuilderInterface $fixtureResponseBuilder, $responseFactory = null)
    {
        parent::__construct($responseFactory);

        $this->fixtureResponseBuilder = $fixtureResponseBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->setDefaultResponse($this->fixtureResponseBuilder->build($request));

        return parent::sendRequest($request);
    }
}
