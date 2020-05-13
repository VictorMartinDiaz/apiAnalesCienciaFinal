<?php

/**
 * PHP version 7.4
 * src/Middleware/JwtMiddleware.php
 *
 * @license ttps://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 *
 * @link    https://odan.github.io/2019/12/02/slim4-oauth2-jwt.html
 */

namespace TDW\ACiencia\Middleware;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TDW\ACiencia\Auth\JwtAuth;
use TDW\ACiencia\Utility\Error;

/**
 * Jwt Middleware
 */
final class JwtMiddleware implements MiddlewareInterface
{
    private ContainerInterface $container;

    private JwtAuth $jwtAuth;

    private ResponseFactoryInterface $responseFactory;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->jwtAuth = $container->get(JwtAuth::class);
        $this->responseFactory = $container->get(ResponseFactoryInterface::class);
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorization = explode(' ', $request->getHeaderLine('Authorization'));
        $token = $authorization[1] ?? '';

        if (!$token || !$this->jwtAuth->validateToken($token)) {
            return Error::error(
                $this->responseFactory->createResponse(),
                StatusCode::STATUS_UNAUTHORIZED
            );
        }

        // Append valid token
        $parsedToken = $this->jwtAuth->createParsedToken($token);
        $request = $request->withAttribute('token', $parsedToken);

        // Append the user id as request attribute
        $request = $request->withAttribute('uid', $parsedToken->getClaim('uid'));

        return $handler->handle($request);
    }
}
