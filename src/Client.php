<?php

namespace Swis\Http\Fixture;

use Http\Message\ResponseFactory;
use Http\Mock\Client as MockClient;
use Psr\Http\Message\RequestInterface;

class Client extends MockClient
{
    /**
     * @var \Swis\Http\Fixture\ResponseBuilderInterface
     */
    protected $fixtureResponseBuilder;

    /**
     * @param \Swis\Http\Fixture\ResponseBuilderInterface $fixtureResponseBuilder
     * @param \Http\Message\ResponseFactory|null          $responseFactory
     */
    public function __construct(ResponseBuilderInterface $fixtureResponseBuilder, ResponseFactory $responseFactory = null)
    {
        parent::__construct($responseFactory);

        $this->fixtureResponseBuilder = $fixtureResponseBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function doSendRequest(RequestInterface $request)
    {
        $this->setDefaultResponse($this->fixtureResponseBuilder->build($request));

        return parent::doSendRequest($request);
    }
}
