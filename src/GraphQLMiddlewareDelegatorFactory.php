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
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;
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
}
