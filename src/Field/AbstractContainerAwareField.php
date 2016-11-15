<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 14/11/16
 * Time: 12.12
 */

namespace GraphQLMiddleware\Field;

use GraphQLMiddleware\Container\ContainerAwareInterface;
use Interop\Container\ContainerInterface;
use Youshido\GraphQL\Field\AbstractField;

abstract class AbstractContainerAwareField extends AbstractField implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

}