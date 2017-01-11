<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 16/11/16
 * Time: 16.24
 */

namespace GraphQLMiddleware;

use GraphQLMiddleware\Error\JsonErrorResponseGenerator;
use Interop\Container\ContainerInterface;
use Zend\Diactoros\Response;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stratigility\Middleware\ErrorHandler;
use Zend\Stratigility\MiddlewarePipe;

class GraphQLMiddlewareDelegatorFactory implements DelegatorFactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        array $options = null)
    {
        $pipeline = new MiddlewarePipe();
        $pipeline->raiseThrowables();

        $pipeline->pipe(new ErrorHandler(new Response(), new JsonErrorResponseGenerator(true)));

        $pipeline->pipe($callback());

        return $pipeline;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator the service locator which requested the service
     * @param string                  $name           the normalized service name
     * @param string                  $requestedName  the requested service name
     * @param callable                $callback       the callback that is responsible for creating the service
     *
     * @return MiddlewarePipe
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        return $this($serviceLocator, $requestedName, $callback);
    }
}