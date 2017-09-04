# GraphQL Psr7 Middleware

This is a graphql middleware implementation, based on  [Youshido/GraphQL](https://github.com/Youshido/GraphQL) graphql pure php implementation.

## Using middleware


```
use GraphQLMiddleware\Execution\Processor;
use App\GraphQL\MySchema;
use GraphQLMiddleware\GraphQLMiddleware;

...
...

//instantiate the graphql schema
$schema = new MySchema();

//get your container implementing container-interop
$container = get_your_container;

//init processor
$processor = new Processor($container, $schema);
$graphqlMiddleware = new GraphQLMiddleware($processor)

$app->pipe("/graphql", $graphqlMiddleware)
```

If your container support factories you can use those provided.

As an example if you use zend `expressive`  you can take advantage of the
configuration package provided using the [`configuration manager`](https://docs.zendframework.com/zend-expressive/cookbook/modular-layout/)

```
$configManager = new ConfigManager([
    GraphQLMiddleware\ModuleConfig::class,
    new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),
]);

```


## Interacting with the graphql middleware

For every request made to `/graphql` URI or containing `Content-Type: application/graphql` header.
At moment only the `GET` and `POST` method are supported. 

## Factories

* `Schema\SchemaFactory` Factory to instantiate the schema object from an array, class or from the container
* `Execution\ProcessorFactory` Factory to instantiate the graphql processor, 
with the `ContainerAwareInterface` ability to let you write your resolver class with the ability to pull service directly from the container.
* `Resolver\ContainerAwareResoverFactory` Factory to automatically inject the container inside you resolver class

## Container aware fields

You can write your schema making the fields aware of the container, this way you 
can retrive a container reference directly from the field and pull from the container
any deps you need to `resolve` the field logic.

Suppose we have a `TodoField` and we are implementing a mutation `AddTodoField`
to store that todo in our backend

```

<?php

namespace App\GraphQL\Mutation\Todo;


use App\GraphQL\Type\TodoType;
use App\Service\TodoService;
use GraphQLMiddleware\Field\AbstractContainerAwareField;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\StringType;

class AddTodoField extends AbstractContainerAwareField
{
    public function build(FieldConfig $config)
    {
        $config->addArguments([
            'title' => new NonNullType(new StringType()),
            'tags' => new ListType(new StringType())
        ]);
    }

    public function resolve($value, array $args, ResolveInfo $info)
    {
        // pull our service from the db
        $todoService = $this->getContainer()->get(TodoService::class);
        // let the service do his job
        return $todoService->create($args['title']);
    }


    public function getType()
    {
        return new ListType(new TodoType());
    }

    public function getName()
    {
        return 'add';
    }
}

```

To retrive todos from our service, we can write a query field this way

```

namespace App\GraphQL\Query\Todo;


use App\GraphQL\Type\TodoType;
use App\Serivice\TodoService;
use GraphQLMiddleware\Field\AbstractContainerAwareField;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\ListType\ListType;


class TodosField extends AbstractContainerAwareField
{
    public function getType()
    {
        return new ListType(new TodoType());
    }

    public function resolve($value, array $args, ResolveInfo $info)
    {
        /** @var $service TodoService */
        $service = $this->getContainer()->get(TodoService::class);
        return $service->findAll();
    }
}

```


## Buisness logic Validation
   
GraphQL provides validation against the schema types, 
at the moment there is no clean path to follow to do application validation.

It's up to you to validate user data before they it your backend. Let's imagine
that in our todo example, the `title` field must not contain whitespaces and 
must be shorter than 10 chars.

GraphQLMiddleware provides some facility to do application validation, directly
in your `type` before the `resolve` method is actually called.

To do this you can implment `GraphQLMiddleware\Validation\ValidatableInterface`.
This interface is already implemented in `AbstractContainerAwareField`.

Let's modifiy our mutation field `AddTodoField` to implement the validation requirements
to the `title` field.

GraphQLMiddleware use `respect/validation` library to handle validation.

In order to modify our `AddTodoField` we must:

* provide validation rules, implementig the `ValidatableInterface::getValidationRules` method
 
```
use Respect\Validation\Validator as v;

class AddTodoField extends AbstractContainerAwareField
{

    ...
    ...
    
    public function resolve($value, array $args, ResolveInfo $info)
    {
        // the $args array is validate based on the
        // validation rules provided by the getValidationRules method
        return $this->getContainer()->get(TodoResolver::class)->create($args['title']);
    }
    
    public function getValidationRules()
    {
        return [
            'title' => v::stringType()->alpha()->length(1,10)->noWhitespace()
        ];
    }
    
    ...
    ...

}
```

This way the processor, before actually call the  `resolve` method, it call the `validate`
method and only if everything goes well the `resolve` method is called.

The `AbstractContainerAwareField` provides a default implementation of the `validate` method.
So you don't need to implement it but you need to provide only the validation rules.
 
### Validation errors response

The server in case of validation errors provides usefull information about those errors.
The actual implementation, push those information in the `errors` payload in this way

> Someone think that the `errors` payload is meant for system errors like `syntax` errors but personally
  i think that is a good place to put informations on what's going on with the requests.
  
Using our `todos` example if we try to add a todo

```
mutation{
    add(title: "this is not @ very good title") {
        id
    }
}
```

The server response is

```
{
  "data": {
    "add": null
  },
  "errors": [
    {
      "message": "Bad Request. Validation failed.",
      "validation_errors": {
        "add": {
          "title": [
            "\"this is not @ very good title\" alphanumeric only allowed",
            "\"this is not @ very good title\" no withespace allowed"
          ]
        }
      },
      "code": 403
    }
  ]
}
```

In the `errors` payload we have the `validation_errors` information that give us
usefull information about what's wrong with our request.
