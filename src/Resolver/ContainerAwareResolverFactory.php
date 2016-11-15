<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 14/11/16
 * Time: 11.44
 */

namespace GraphQLMiddleware\Resolver;

use Interop\Container\ContainerInterface;

final class ContainerAwareResolverFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new $requestedName($container);
    }

}