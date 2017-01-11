<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 14/11/16
 * Time: 10.23
 */

namespace GraphQLMiddleware\Execution;


use Interop\Container\ContainerInterface;
use Youshido\GraphQL\Schema\AbstractSchema;

final class ProcessorFactory
{
    public function __invoke(ContainerInterface $container, $requestedName)
    {
        /** @var AbstractSchema $schema */
        $schema = $container->get("graphql.schema");

        $processor = new Processor($container, $schema);

        return $processor;
    }
}