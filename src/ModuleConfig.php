<?php

namespace GraphQLMiddleware;

use GraphQLMiddleware\Execution\ProcessorFactory;
use GraphQLMiddleware\Schema;

class ModuleConfig
{
    public function __invoke()
    {
        return [
            "dependencies" => [
                "factories" => [
                    'graphql.processor' => ProcessorFactory::class,
                    'graphql.schema'    => Schema\SchemaFactory::class,
                    GraphQLMiddleware::class => GraphQLMiddlewareFactory::class,
                    GraphQLMiddlewareDelegatorFactory::class =>  GraphQLMiddlewareDelegatorFactory::class,
                ],
                'delegators' => [
                    GraphQLMiddleware::class => [
                        GraphQLMiddlewareDelegatorFactory::class
                    ]
                ],
            ],

            "middleware_pipeline" => [
                'graphql' => [
                    'middleware' => [
                        GraphQLMiddleware::class
                    ],
                    'priority' => 1000,
                ],
            ]
        ];
    }
}
