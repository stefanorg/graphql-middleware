<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 15/11/16
 * Time: 14.14
 */

namespace GraphQLMiddleware\Exception;

use Interop\Container\Exception\ContainerException;
use RuntimeException as SplRuntimeException;

class ServiceNotCreatedException extends SplRuntimeException implements ContainerException
{

    public static function invalidSchemaConfigurationProvided()
    {
        return new static (
            "Schema not found. You must set schema paramenter in graphql configuration."
        );
    }

    public static function invalidSchemaProvided($schema)
    {
        return new static (
            sprintf(
                "Invalid Schema '%s' provided. Expected array|string got '%s'",
                $schema,
                is_object($schema) ? get_class($schema) : gettype($schema)
            )
        );
    }

    public static function schemaNotFound($schema)
    {
        return new static (
            "No class found form schema {$schema}. You must provide an instantiable classname or a classname registered in your container."
        );
    }
}