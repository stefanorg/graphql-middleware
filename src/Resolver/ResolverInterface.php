<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 14/11/16
 * Time: 13.21
 */

namespace GraphQLMiddleware\Resolver;

use Youshido\GraphQL\Execution\ResolveInfo;

interface ResolverInterface
{
    public function resolve($value, array $args, ResolveInfo $info);
}