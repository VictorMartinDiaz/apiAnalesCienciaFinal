<?php

/**
 * PHP version 7.4
 * src/Controller/Product/ProductController.php
 */

namespace TDW\ACiencia\Controller\Product;

use Psr\Http\Message\RequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Controller\ElementBaseController;
use TDW\ACiencia\Entity\Product;

/**
 * Class ProductController
 */
class ProductController extends ElementBaseController
{
    /** @var string ruta api gestión productos  */
    public const PATH_PRODUCTS = '/products';

    /**
     * Summary: Returns all products
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function cget(Request $request, Response $response): Response
    {
        return $this->getAllElements($response, Product::class, 'products');
    }

    /**
     * Summary: Returns a product based on a single productId
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function get(Request $request, Response $response, array $args): Response
    {
        return $this->getElementById($response, Product::class, $args['productId']);
    }

    /**
     * Summary: Returns status code 204 if productname exists
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getProductname(Request $request, Response $response, array $args): Response
    {
        return $this->getElementByName($response, Product::class, $args['productname']);
    }

    /**
     * Summary: Deletes a product
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        return $this->opDelete($request, $response, Product::class, $args['productId']);
    }

    /**
     * Summary: Creates a new product
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function post(Request $request, Response $response): Response
    {
        return $this->opPost($request, $response, Product::class);
    }

    /**
     * Summary: Updates a product
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function put(Request $request, Response $response, array $args): Response
    {
        $args['id'] = $args['productId'];
        return $this->opPut($request, $response, $args, Product::class);
    }
}
