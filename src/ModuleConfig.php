<?php

/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 15/11/16
 * Time: 11.17
 */

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
                    GraphQLMiddleware::class => GraphQLMiddlewareFactory::class
                ]
            ],
            "middleware_pipeline" => [
                [
                    'middleware' => [
                        GraphQLMiddleware::class,
                    ],
                    'priority' => 1000,
                ],
            ]
        ];
    }
}