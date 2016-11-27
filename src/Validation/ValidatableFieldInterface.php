<?php
/**
 * Created by PhpStorm.
 * User: n4z4
 * Date: 26/11/16
 * Time: 11:43
 */

namespace GraphQLMiddleware\Validation;


use Youshido\GraphQL\Execution\ResolveInfo;

interface ValidatableFieldInterface
{

    /**
     * An array map to define field validation rules
     * @return array
     */
    public function getValidationRules();

    /**
     * Array map with custom messages template
     * for respect/validation custom messages
     * @return array
     */
    public function getCustomValidationMessages();

    public function validate(array $args, ResolveInfo $info);
}