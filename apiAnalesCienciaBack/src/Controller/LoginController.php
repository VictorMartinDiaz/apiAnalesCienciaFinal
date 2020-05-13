<?php

/**
 * PHP version 7.4
 * src/Controller//LoginController.php
 */

namespace TDW\ACiencia\Controller;

use Doctrine\ORM\EntityManager;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Auth\JwtAuth;
use TDW\ACiencia\Entity\User;
use TDW\ACiencia\Utility\Error;

/**
 * Class CuestionController
 */
class LoginController
{
    protected ContainerInterface $container;

    protected EntityManager $entityManager;

    private JwtAuth $jwtAuth;

    // constructor receives container instance
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->entityManager = $container->get(EntityManager::class);
        $this->jwtAuth = $container->get(JwtAuth::class);
    }

    /**
     * POST /access_token
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function post(Request $request, Response $response): Response
    {
        $req_data
            = $request->getParsedBody()
            ?? json_decode($request->getBody(), true, 3, JSON_INVALID_UTF8_IGNORE);

        /** @var User $user */
        $user = null;
        if (isset($req_data['username'], $req_data['password'])) {
            $user = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy([ 'username' => $req_data['username'] ]);
        }

        if (null === $user || !$user->validatePassword($req_data['password'])) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        if (!array_key_exists('scope', $req_data)) {
            $token = $this->jwtAuth->createJwt($user);
        } else {
            $scopes = (array) preg_split('/ |(\+)/', $req_data['scope']);
            $token = $this->jwtAuth->createJwt($user, $scopes);
        }

        return $response
            ->withJson([
                'token_type' => 'Bearer',
                'expires_in' => $this->jwtAuth->getLifetime(),    // 14400
                'access_token' => $token
            ])
            ->withHeader('Authorization', 'Bearer ' . $token);
    }
}
