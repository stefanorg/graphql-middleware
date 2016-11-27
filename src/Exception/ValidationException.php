<?php
/**
 * Created by PhpStorm.
 * User: n4z4
 * Date: 26/11/16
 * Time: 14:02
 */

namespace GraphQLMiddleware\Exception;

use Exception as BaseException;

class ValidationException extends BaseException
{

    public static function create($message, $code = 500)
    {
        return new static (
            $message, $code
        );
    }
}