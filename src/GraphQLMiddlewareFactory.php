<?php

namespace GraphQLMiddleware;

use Interop\Container\ContainerInterface;

class GraphQLMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $processor = $container->get("graphql.processor");

        return new GraphQLMiddleware($processor);
    }
}
