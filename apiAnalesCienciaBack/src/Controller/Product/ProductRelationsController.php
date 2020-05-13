<?php

/**
 * PHP version 7.4
 * src/Controller/Product/ProductRelationsController.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller\Product;

use Psr\Http\Message\RequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Controller\ElementRelationsBaseController;
use TDW\ACiencia\Entity\Entity;
use TDW\ACiencia\Entity\Person;
use TDW\ACiencia\Entity\Product;

/**
 * Class ProductRelationsController
 */
final class ProductRelationsController extends ElementRelationsBaseController
{
    /**
     * Summary: GET /products/{productId}/entities
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getEntities(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'entityName' => Product::class,
            'elementId' => $args['productId'],
            'getter' => 'getEntities',
            'stuff' => 'entities',
        ];
        return $this->getElements($response, $elementData);
    }

    /**
     * PUT /products/{productId}/entities/add/{stuffId}
     * PUT /products/{productId}/entities/rem/{stuffId}
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function operationEntity(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'entityName' => Product::class,
            'elementId' => $args['productId'],
            'stuffEName' => Entity::class,
            'stuffId' => $args['stuffId'],
            'getter' => 'getEntities',
            'stuff' => 'entities',
        ];
        return $this->operationStuff($request, $response, $elementData);
    }

    /**
     * Summary: GET /products/{productId}/persons
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getPersons(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'entityName' => Product::class,
            'elementId' => $args['productId'],
            'getter' => 'getPersons',
            'stuff' => 'persons',
        ];
        return $this->getElements($response, $elementData);
    }

    /**
     * PUT /products/{productId}/persons/add/{stuffId}
     * PUT /products/{productId}/persons/rem/{stuffId}
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function operationPerson(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'entityName' => Product::class,
            'elementId' => $args['productId'],
            'stuffEName' => Person::class,
            'stuffId' => $args['stuffId'],
            'getter' => 'getPersons',
            'stuff' => 'persons',
        ];
        return $this->operationStuff($request, $response, $elementData);
    }
}
