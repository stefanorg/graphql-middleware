<?php

namespace GraphQLMiddleware\Container;

use Interop\Container\ContainerInterface;

interface ContainerAwareInterface
{

    /**
     * @param ContainerInterface $container
     * @return self
     */
    public function setContainer(ContainerInterface $container);

    /**
     * @return ContainerInterface
     */
    public function getContainer();

}