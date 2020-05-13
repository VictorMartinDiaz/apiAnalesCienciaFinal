<?php

/**
 * PHP version 7.4
 * src/Controller/Entity/EntityRelationsController.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller\Entity;

use Psr\Http\Message\RequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Controller\ElementRelationsBaseController;
use TDW\ACiencia\Entity\Entity;
use TDW\ACiencia\Entity\Person;
use TDW\ACiencia\Entity\Product;
use TDW\ACiencia\Utility\Error;

/**
 * Class EntityRelationsController
 */
final class EntityRelationsController extends ElementRelationsBaseController
{
    /**
     * Summary: GET /entities/{entityId}/persons
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getPersons(Request $request, Response $response, array $args): Response
    {
        $elementData = [
          'entityName' => Entity::class,
          'elementId' => $args['entityId'],
          'getter' => 'getPersons',
          'stuff' => 'persons',
        ];
        return $this->getElements($response, $elementData);
    }


    /**
     * PUT /entities/{entityId}/persons/add/{stuffId}
     * PUT /entities/{entityId}/persons/rem/{stuffId}
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function operationPerson(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'entityName' => Entity::class,
            'elementId' => $args['entityId'],
            'stuffEName' => Person::class,
            'stuffId' => $args['stuffId'],
            'getter' => 'getPersons',
            'stuff' => 'persons',
        ];
        return $this->operationStuff($request, $response, $elementData);
    }

    /**
     * Summary: GET /entities/{entityId}/products
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getProducts(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'entityName' => Entity::class,
            'elementId' => $args['entityId'],
            'getter' => 'getProducts',
            'stuff' => 'products',
        ];
        return $this->getElements($response, $elementData);
    }

    /**
     * PUT /entities/{entityId}/products/add/{stuffId}
     * PUT /entities/{entityId}/products/rem/{stuffId}
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function operationProduct(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'entityName' => Entity::class,
            'elementId' => $args['entityId'],
            'stuffEName' => Product::class,
            'stuffId' => $args['stuffId'],
            'getter' => 'getProduct',
            'stuff' => 'products',
        ];
        return $this->operationStuff($request, $response, $elementData);
    }
}
