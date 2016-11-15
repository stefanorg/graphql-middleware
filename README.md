# GraphQL Psr7 Middleware

This is a graphql middleware implementation, based on  [Youshido/GraphQL](https://github.com/Youshido/GraphQL) graphql pure php implementation.

## Interacting with the graphql middleware

For every request made to `/graphql` URI or containing `Content-Type: application/graphql` header.
At moment only the `GET` and `POST` method are supported. 

## Factories

* `Schema\SchemaFactory` Factory to instantiate the schema object from an array, class or from the container
* `Execution\ProcessorFactory` Factory to instantiate the graphql processor, 
with the `ContainerAwareInterface` ability to let you write your resolver class with the ability to pull service directly from the container.
* `Resolver\ContainerAwareResoverFactory` Factory to automatically inject the container inside you resolver class

