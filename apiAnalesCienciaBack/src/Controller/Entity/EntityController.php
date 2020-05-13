<?php

/**
 * PHP version 7.4
 * src/Controller/Entity/EntityController.php
 */

namespace TDW\ACiencia\Controller\Entity;

use Psr\Http\Message\RequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Controller\ElementBaseController;
use TDW\ACiencia\Entity\Entity;
use TDW\ACiencia\Utility\Error;

/**
 * Class EntityController
 */
class EntityController extends ElementBaseController
{
    /** @var string ruta api gestión entityas  */
    public const PATH_ENTITIES = '/entities';

    /**
     * Summary: Returns all entities
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function cget(Request $request, Response $response): Response
    {
        return $this->getAllElements($response, Entity::class, 'entities');
    }

    /**
     * Summary: Returns a entity based on a single entityId
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function get(Request $request, Response $response, array $args): Response
    {
        return $this->getElementById($response, Entity::class, $args['entityId']);
    }

    /**
     * Summary: Returns status code 204 if entityname exists
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getEntityname(Request $request, Response $response, array $args): Response
    {
        return $this->getElementByName($response, Entity::class, $args['entityname']);
    }

    /**
     * Summary: Deletes a entity
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        return $this->opDelete($request, $response, Entity::class, $args['entityId']);
    }

    /**
     * Summary: Creates a new entity
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function post(Request $request, Response $response): Response
    {
        return $this->opPost($request, $response, Entity::class);
    }

    /**
     * Summary: Updates a entity
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function put(Request $request, Response $response, array $args): Response
    {
        $args['id'] = $args['entityId'];
        return $this->opPut($request, $response, $args, Entity::class);
    }
}
