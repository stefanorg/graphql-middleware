<?php

namespace GraphQLMiddleware\Schema;

use GraphQLMiddleware\Exception\ServiceNotCreatedException;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Youshido\GraphQL\Schema\AbstractSchema;

class SchemaFactory {

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get("config");

        if (!is_string($config['graphql']['schema'])) {
            throw ServiceNotCreatedException::invalidSchemaConfigurationProvided();
        }

        $schema = $config['graphql']['schema'];

        // inline style
        if (is_array($schema)) {
            return new Schema($schema);
        }

        // object style
        if (is_string($schema)) {
            return $this->getSchemaFromClassname($schema, $container);
        }

        throw ServiceNotCreatedException::invalidSchemaProvided($schema);
    }

    private function getSchemaFromClassname(string $className, ContainerInterface $container) : AbstractSchema
    {
        if ($container->has($className)) {
            return $container->get($className);
        }

        if (class_exists($className)) {
            return new $className();
        }

        throw ServiceNotCreatedException::schemaNotFound($className);
    }
}