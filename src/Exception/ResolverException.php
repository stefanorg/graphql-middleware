<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 17/11/16
 * Time: 12.16
 */

namespace GraphQLMiddleware\Exception;

use Interop\Container\Exception\ContainerException;
use RuntimeException as SplRuntimeException;

class ResolverException extends SplRuntimeException implements ContainerException
{

    public static function invalidResolver($field, $resolver)
    {
        return new static (
            "The provided resolver {$resolver} for field {$field} is not valid. Resolver must implement the `resolve` method"
        );
    }

}