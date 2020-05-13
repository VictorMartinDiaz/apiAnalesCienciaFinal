<?php

/**
 * PHP version 7.4
 * src/Controller/ElementRelationsBaseController.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Message\RequestInterface as Request;
use Slim\Http\Response;
use TDW\ACiencia\Entity\Element;
use TDW\ACiencia\Utility\Error;

/**
 * Class ElementBaseController
 */
class ElementRelationsBaseController extends ElementBaseController
{
    /**
     * Summary: get a list of related items
     * e.g.: GET /products/{productId}/entities
     *
     * @param Response $response
     * @param array $elementData
     *  - 'entityName' => (string) Owning class name (e.g. Product::class)
     *  - 'elementId' => (int) element Id,
     *  - 'getter' => (string) getter function ('getEntities')
     *  - 'stuff' => (string) items tag (e.g. 'entities')
     * @return Response
     */
    public function getElements(Response $response, array $elementData): Response
    {
        /** @var Element $element */
        $element = $this->entityManager
            ->getRepository($elementData['entityName'])
            ->find($elementData['elementId']);

        if (null === $element) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        return $response
            ->withAddedHeader('ETag', md5(serialize($element)))
            ->withJson([$elementData['stuff'] => $element->{$elementData['getter']}()]);
    }

    /**
     * Add and remove relationships between elements
     * e.g.: PUT /products/{productId}/entities/add/{stuffId}
     * e.g.: PUT /products/{productId}/entities/rem/{stuffId}
     *
     * @param Request $request
     * @param Response $response
     * @param array $elementData
     *  - 'entityName' => (string) Owning class name (e.g. Product::class)
     *  - 'elementId' => (int) owning element Id (e.g. productId)
     *  - 'stuffEName' => (string) Inversed class name (Entity::class)
     *  - 'stuffId' => (int) inversed element Id
     *  - 'getter' => (string) getter function ('getEntities')
     *  - 'stuff' => (string) items tag (e.g. 'entities')
     * @return Response
     */
    public function operationStuff(Request $request, Response $response, array $elementData): Response
    {
        if (!$this->checkWriterScope($request)) { // 403
            return Error::error($response, StatusCode::STATUS_FORBIDDEN);
        }

        /** @var Element $element */
        $element = $this->entityManager
            ->getRepository($elementData['entityName'])->find($elementData['elementId']);

        if (null === $element) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        $stuff = $this->entityManager
            ->getRepository($elementData['stuffEName'])->find($elementData['stuffId']);
        if (null === $stuff) {     // 406
            return Error::error($response, StatusCode::STATUS_NOT_ACCEPTABLE);
        }

        $endPoint = $request->getUri()->getPath();
        $segments = explode('/', $endPoint);
        $operationAdd = 'add' . $this->className($elementData['stuffEName']);
        $operationRem = 'remove' . $this->className($elementData['stuffEName']);
        ('add' === $segments[array_key_last($segments) - 1])
            ? $element->{$operationAdd}($stuff)
            : $element->{$operationRem}($stuff);
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
        }

        return $response
            ->withStatus(209, 'Content Returned')
            ->withJson($element);
    }

    /**
     * @param string $fqcn  Fully Qualified Class Name
     *
     * @return string Class Name
     */
    private function className(string $fqcn): string
    {
        $elements = explode('\\', $fqcn);
        return $elements[array_key_last($elements)];
    }
}
