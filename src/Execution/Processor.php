<?php

namespace GraphQLMiddleware\Execution;

use GraphQLMiddleware\Container\ContainerAwareInterface;
use GraphQLMiddleware\Execution\Context\ExecutionContext;
use GraphQLMiddleware\Resolver\ResolverInterface;
use GraphQLMiddleware\Validation\ValidatableFieldInterface;
use Interop\Container\ContainerInterface;
use Youshido\GraphQL\Execution\Processor as BaseProcessor;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Field\FieldInterface;
use Youshido\GraphQL\Parser\Ast\Field as AstField;
use Youshido\GraphQL\Parser\Ast\Interfaces\FieldInterface as AstFieldInterface;
use Youshido\GraphQL\Parser\Ast\Mutation;
use Youshido\GraphQL\Parser\Ast\Query as AstQuery;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\TypeService;
use Youshido\GraphQL\Validator\Exception\ResolveException;


class Processor extends BaseProcessor
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Processor constructor.
     * @param ContainerInterface $container
     * @param $schema AbstractSchema The schema instance
     */
    public function __construct(ContainerInterface $container, AbstractSchema $schema)
    {
        $this->container = $container;
        $this->executionContext = new ExecutionContext($schema);

        parent::__construct($this->executionContext->getSchema());
    }

    protected function doResolve(FieldInterface $field, AstFieldInterface $ast, $parentValue = null)
    {

        /** @var AstQuery|AstField $ast */
        $arguments = $this->parseArgumentsValues($field, $ast);
        $astFields = $ast instanceof AstQuery ? $ast->getFields() : [];
        $resolveInfo = $this->createResolveInfo($field, $astFields);

        if ($this->shouldInjectContainer($field)) {
            $field->setContainer($this->container);
        }

        //allow userland validation for mutation args
        if ($ast instanceof Mutation) {
            if ($field instanceof ValidatableFieldInterface) {

                $field->validate($arguments, $resolveInfo);

                if ($this->getExecutionContext()->hasErrors()) {
                    return null;
                }
            }
        }

        if ($field instanceof Field) {
            if ($resolveFunc = $field->getConfig()->getResolveFunction()) {

                if ($this->isServiceReference($resolveFunc)) {
                    $service = $this->getResolverService($resolveFunc, $field->getName());

                    $method = $this->getMethodName($resolveFunc);

                    return $service->$method($parentValue, $arguments, $resolveInfo);
                }

                return $resolveFunc($parentValue, $arguments, $resolveInfo);
            } else {
                return TypeService::getPropertyValue($parentValue, $field->getName());
            }
        } else {

            return $field->resolve($parentValue, $arguments, $resolveInfo);
        }
    }

    private function shouldInjectContainer($field) {
        return in_array(ContainerAwareInterface::class, class_implements($field));
    }

    /**
     * @param $service
     * @param $fieldName
     * @return \GraphQLMiddleware\Resolver\ResolverInterface
     * @throws ResolveException
     */
    private function getResolverService($service, $fieldName) {

        $service = $this->getServiceName($service);

        if (!$this->container->has($service)) {
            throw new ResolveException(sprintf('Resolve service "%s" not found for field "%s"', $service, $fieldName));
        }

        $serviceInstance = $this->container->get($service);

        if (!in_array(ResolverInterface::class, class_implements($service))) {
            throw new ResolveException(sprintf('Resolver service "%s" for field "%s" must implement interface "%s"', $service, $fieldName, ResolverInterface::class));
        }

        return $serviceInstance;
    }

    private function getServiceName($resolveFunc)
    {
        if (is_array($resolveFunc)){
            $resolveFunc = $resolveFunc[0];
        }

        return $resolveFunc;
    }

    /**
     * Return the resolve method name as specified in configuration.
     * If none found return the "resolve" method name as default
     * @param $resolveFunc
     * @return mixed|string
     */
    private function getMethodName($resolveFunc) {
        if (is_array($resolveFunc) && count($resolveFunc)===2) {
            return $resolveFunc[1];
        }
        return "resolve";
    }

    private function isServiceReference($resolveFunc)
    {
        $service = $this->getServiceName($resolveFunc);
        return is_string($service) && $this->container->has($service);
    }

}