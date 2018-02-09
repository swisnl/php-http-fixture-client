<?php

namespace Swis\Http\Fixture;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ResponseBuilderInterface
{
    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function build(RequestInterface $request): ResponseInterface;
}
