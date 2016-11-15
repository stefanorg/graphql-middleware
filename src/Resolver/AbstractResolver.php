<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 14/11/16
 * Time: 12.30
 */

namespace GraphQLMiddleware\Resolver;


abstract class AbstractResolver implements ResolverInterface
{

    private $container;

    /**
     * AbstractResolver constructor.
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

}