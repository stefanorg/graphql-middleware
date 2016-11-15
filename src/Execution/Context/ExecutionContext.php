<?php

namespace GraphQLMiddleware\Execution\Context;

use Youshido\GraphQL\Execution\Context\ExecutionContext as BaseContext;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Validator\ConfigValidator\ConfigValidator;
use Youshido\GraphQL\Validator\ConfigValidator\Rules\TypeValidationRule;


class ExecutionContext extends BaseContext
{
    public function __construct(AbstractSchema $schema)
    {
        $validator = ConfigValidator::getInstance();

        $validator->addRule('type', new TypeValidationRule($validator));

        parent::__construct($schema);
    }

}