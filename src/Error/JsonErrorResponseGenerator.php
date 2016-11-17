<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 16/11/16
 * Time: 16.47
 */

namespace GraphQLMiddleware\Error;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

final class JsonErrorResponseGenerator
{
    /**
     * @var string The graphql uri path to match against
     */
    private $graphql_uri = "/graphql";

    /**
     * @var array The graphql headers
     */
    private $graphql_headers = [
        "application/graphql"
    ];

    private $isDevelopment;

    /**
     * JsonErrorResponseGenerator constructor.
     * @param $isDevelopment
     */
    public function __construct($isDevelopment = false)
    {
        $this->isDevelopment = $isDevelopment;
    }


    /**
     * @param $e \Throwable
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return JsonResponse
     */
    public function __invoke($e, ServerRequestInterface $request, ResponseInterface $response)
    {
        // handling response if is a graphql request
        if ($this->isGraphQLRequest($request)) {
            $errors = [
                'message' => $e->getMessage(),
            ];

            if ($this->isDevelopment) {
                $errors['file'] = $e->getFile();
                $errors['line'] = $e->getLine();
                $errors['code'] = $e->getCode();
                $errors['trace'] = $e->getTrace();
            }

            $res = new JsonResponse([
                'errors'=> $errors
            ],500);
            return $res;
        }
    }

    private function isGraphQLRequest(ServerRequestInterface $request) {
        return $this->hasUri($request) || $this->hasGraphQLHeader($request);
    }

    private function hasUri(ServerRequestInterface $request)
    {
        return  $this->graphql_uri === $request->getUri()->getPath();
    }

    private function hasGraphQLHeader(ServerRequestInterface $request)
    {
        if (!$request->hasHeader('content-type')) {
            return false;
        }

        $request_headers = explode(",", $request->getHeaderLine("content-type"));

        foreach ($this->graphql_headers as $allowed_header) {
            if (in_array($allowed_header, $request_headers)){
                return true;
            }
        }

        return  false;
    }
}