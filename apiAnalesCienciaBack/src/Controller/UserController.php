<?php

/**
 * PHP version 7.4
 * src/Controller/UserController.php
 */

namespace TDW\ACiencia\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Lcobucci\JWT\Token;
use OutOfRangeException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface as Request;
use Slim\Http\Response;
use Slim\Routing\RouteContext;
use TDW\ACiencia\Entity\Role;
use TDW\ACiencia\Entity\User;
use TDW\ACiencia\Utility\Error;

/**
 * Class UserController
 */
class UserController
{
    /** @var string ruta api gestión usuarios  */
    public const PATH_USERS = '/users';

    protected ContainerInterface $container;

    protected EntityManager $entityManager;

    // constructor receives container instance
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->entityManager = $container->get(EntityManager::class);
    }

    /**
     * Summary: Returns all users
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function cget(Request $request, Response $response): Response
    {
        $users = $this->entityManager
            ->getRepository(User::class)
            ->findAll();

        if (0 === count($users)) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        return $response
            ->withAddedHeader('ETag', md5(serialize($users)))
            ->withJson([ 'users' => $users ]);
    }

    /**
     * Summary: Returns a user based on a single userId
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function get(Request $request, Response $response, array $args): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($args['userId']);
        if (null === $user) {
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        return $response
            ->withAddedHeader('ETag', md5(serialize($user)))
            ->withJson($user);
    }

    /**
     * Summary: Returns status code 204 if username exists
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getUsername(Request $request, Response $response, array $args): Response
    {
        $usuario = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy([ 'username' => $args['username'] ]);

        if (null === $usuario) {
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        return $response
            ->withStatus(StatusCode::STATUS_NO_CONTENT);  // 204
    }

    /**
     * Summary: Deletes a user
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ORMException
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        // Error 403 scope: writer
        if (false === $this->checkTokenScope($request->getAttribute('token'), Role::ROLE_WRITER)) {
            return Error::error($response, StatusCode::STATUS_FORBIDDEN);
        }

        $user = $this->entityManager->getRepository(User::class)->find($args['userId']);

        if (null === $user) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush($user);

        return $response
            ->withStatus(StatusCode::STATUS_NO_CONTENT);  // 204
    }

    /**
     * Summary: Provides the list of HTTP supported methods
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    public function options(Request $request, Response $response): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $routingResults = $routeContext->getRoutingResults();
        $methods = $routingResults->getAllowedMethods();

        return $response
            ->withAddedHeader(
                'Allow',
                implode(', ', $methods)
            );
    }

    /**
     * Summary: Creates a new user
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ORMException
     */
    public function post(Request $request, Response $response): Response
    {
        // Error 403 scope: writer
        if (false === $this->checkTokenScope($request->getAttribute('token'), Role::ROLE_WRITER)) {
            return Error::error($response, StatusCode::STATUS_FORBIDDEN);
        }

        $req_data
            = $request->getParsedBody()
            ?? json_decode($request->getBody(), true);
        $req_data = $req_data ?? [];

        if (!isset($req_data['username'], $req_data['email'], $req_data['password'])) { // 422 - Faltan datos
            return Error::error($response, StatusCode::STATUS_UNPROCESSABLE_ENTITY);
        }

        // hay datos -> procesarlos
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria
            ->where($criteria::expr()->eq('username', $req_data['username']))
            ->orWhere($criteria::expr()->eq('email', $req_data['email']));
        // STATUS_BAD_REQUEST 400: username or e-mail already exists
        if ($this->entityManager->getRepository(User::class)->matching($criteria)->count()) {
            return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
        }

        // 201
        $user = new User(
            $req_data['username'],
            $req_data['email'],
            $req_data['password'],
            $req_data['role'] ?? Role::ROLE_READER
        );
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);

        return $response
            ->withAddedHeader(
                'Location',
                $request->getUri()->getPath() . '/' . $user->getId()
            )
            ->withJson($user, StatusCode::STATUS_CREATED);
    }

    /**
     * Summary: Updates a user
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ORMException
     */
    public function put(Request $request, Response $response, array $args): Response
    {
        // Error 403 scope: writer
        if (false === $this->checkTokenScope($request->getAttribute('token'), Role::ROLE_WRITER)) {
            return Error::error($response, StatusCode::STATUS_FORBIDDEN);
        }

        $req_data
            = $request->getParsedBody()
            ?? json_decode($request->getBody(), true);
        $req_data = $req_data ?? [];
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($args['userId']);

        if (null === $user) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        if (isset($req_data['username'])) {
            $usuarioId = $this->findIdBy('username', $req_data['username']);
            if ($usuarioId && $args['userId'] != $usuarioId) {
                // 400 BAD_REQUEST: username already exists
                return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
            }
            $user->setUsername($req_data['username']);
        }

        if (isset($req_data['email'])) {
            $usuarioId = $this->findIdBy('email', $req_data['email']);
            if ($usuarioId && $args['userId'] !== $usuarioId) {
                // 400 BAD_REQUEST: e-mail already exists
                return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
            }
            $user->setEmail($req_data['email']);
        }

        // password
        if (isset($req_data['password'])) {
            $user->setPassword($req_data['password']);
        }

        // role
        if (isset($req_data['role'])) {
            try {
                $user->setRole($req_data['role']);
            } catch (OutOfRangeException $e) {    // 400 BAD_REQUEST: role Out or Range
                return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
            }
        }

        $this->entityManager->flush($user);

        return $response
            ->withStatus(209, 'Content Returned')
            ->withJson($user);
    }

    /**
     * Determines if a value exists for an attribute
     *
     * @param string $attr attribute
     * @param string $value value
     * @return int
     */
    private function findIdBy(string $attr, string $value): int
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([ $attr => $value ]);
        return $user ? $user->getId() : 0;
    }

    /**
     * @param Token $token
     * @param string $scope
     *
     * @return bool
     */
    private function checkTokenScope(Token $token, string $scope): bool
    {
        $scopes = $token->getClaim('scopes', null);
        return (bool) in_array($scope, $scopes, true);
    }
}
