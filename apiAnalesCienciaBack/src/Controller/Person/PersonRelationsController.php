<?php

/**
 * PHP version 7.4
 * src/Controller/Person/PersonRelationsController.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller\Person;

use Psr\Http\Message\RequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Controller\ElementRelationsBaseController;
use TDW\ACiencia\Entity\Entity;
use TDW\ACiencia\Entity\Person;
use TDW\ACiencia\Entity\Product;
use TDW\ACiencia\Utility\Error;

/**
 * Class PersonRelationsController
 */
final class PersonRelationsController extends ElementRelationsBaseController
{
    /**
     * Summary: GET /persons/{personId}/entities
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getEntities(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'entityName' => Person::class,
            'elementId' => $args['personId'],
            'getter' => 'getEntities',
            'stuff' => 'entities',
        ];
        return $this->getElements($response, $elementData);
    }

    /**
     * PUT /persons/{personId}/entities/add/{stuffId}
     * PUT /persons/{personId}/entities/rem/{stuffId}
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
            'entityName' => Person::class,
            'elementId' => $args['personId'],
            'stuffEName' => Entity::class,
            'stuffId' => $args['stuffId'],
            'getter' => 'getEntities',
            'stuff' => 'entities',
        ];
        return $this->operationStuff($request, $response, $elementData);
    }

    /**
     * Summary: GET /persons/{personId}/products
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getProducts(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'entityName' => Person::class,
            'elementId' => $args['personId'],
            'getter' => 'getProducts',
            'stuff' => 'products',
        ];
        return $this->getElements($response, $elementData);
    }

    /**
     * PUT /persons/{personId}/products/add/{stuffId}
     * PUT /persons/{personId}/products/rem/{stuffId}
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function operationProduct(Request $request, Response $response, array $args): Response
    {
        $elementData = [
            'entityName' => Person::class,
            'elementId' => $args['personId'],
            'stuffEName' => Product::class,
            'stuffId' => $args['stuffId'],
            'getter' => 'getProduct',
            'stuff' => 'products',
        ];
        return $this->operationStuff($request, $response, $elementData);
    }
}
