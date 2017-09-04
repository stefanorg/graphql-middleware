<?php

namespace GraphQLMiddleware\Field;

use GraphQLMiddleware\Container\ContainerAwareInterface;
use GraphQLMiddleware\Validation\ValidatableFieldInterface;
use Interop\Container\ContainerInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Validator;
use Youshido\GraphQL\Exception\DatableResolveException;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;

abstract class AbstractContainerAwareField extends AbstractField implements ContainerAwareInterface, ValidatableFieldInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $validation_errors = [];

    /**
     * @param ContainerInterface $container
     * @return $this
     */
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

    /**
     * Customize validation exception based on type of exception
     *
     * @param \Exception $e
     * @param string     $field_name
     */
    private function prepareValidationException(\Exception $e, $field_name) {

        if ($e instanceof NestedValidationException) {

            $data = !empty($this->getCustomValidationMessages())
                ? array_values(array_filter($e->findMessages($this->getCustomValidationMessages()),
                    function($val) {
                        return !empty($val);
                    }))
                : $data =$e->getMessages();

        } elseif ($e instanceof RespectValidationException) {

            $data = $e->getMainMessage();

        } else{

            $data = $e->getMessage();

        }

        $this->validation_errors[$this->getName()][$field_name] = $data;

    }

    public function getValidationRules()
    {
        return [];
    }

    public function getCustomValidationMessages()
    {
        return [];
    }

    /**
     * Execute userland validation based on user defined validation rules
     * @param array       $args
     * @param ResolveInfo $info
     * @param bool        $stopOnFirstError
     */
    public function validate(array $args, ResolveInfo $info, $stopOnFirstError = true)
    {
        $rules = $this->getValidationRules();
        foreach ($args as $field_name => $field_value) {
            try{
                /** @var Validator $validator */
                $validator = $rules[$field_name];

                //no validator for field
                if (!$validator) continue;

                //validate
                $stopOnFirstError
                    ? $validator->check($field_value)
                    : $validator->assert($field_value);

            }catch (\Exception $ve) {
                $this->prepareValidationException($ve, $field_name);
            }
        }

        if (!empty($this->validation_errors)) {
            $info->getExecutionContext()->addError(
                new DatableResolveException(
                    "Bad Request. Validation failed.",
                    403,
                    [
                        "validation_errors" => $this->validation_errors
                    ]
                )
            );
        }
    }
}
