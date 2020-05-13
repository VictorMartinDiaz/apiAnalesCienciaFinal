<?php

/**
 * PHP version 7.4
 * src/Controller/ElementBaseController.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Controller;

use DateTime;
use Doctrine\ORM\EntityManager;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface as Request;
use Slim\Http\Response;
use Slim\Routing\RouteContext;
use TDW\ACiencia\Entity\Element;
use TDW\ACiencia\Entity\Role;
use TDW\ACiencia\Utility\Error;

/**
 * Class ElementBaseController
 */
class ElementBaseController
{
    protected ContainerInterface $container;

    protected EntityManager $entityManager;

    // constructor receives container instance
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->entityManager = $container->get(EntityManager::class);
    }

    /**
     * Summary: Returns all elements
     *
     * @param Response $response
     * @param string $entityName
     * @param string $tag array key
     * @return Response
     */
    public function getAllElements(Response $response, string $entityName, string $tag): Response
    {
        $elements = $this->entityManager
            ->getRepository($entityName)
            ->findAll();

        if (0 === count($elements)) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        return $response
            ->withAddedHeader('ETag', md5(serialize($elements)))
            ->withJson([ $tag => $elements ]);
    }

    /**
     * Summary: Returns a element based on a single id
     *
     * @param Response $response
     * @param string $entityName
     * @param int $id
     * @return Response
     */
    public function getElementById(Response $response, string $entityName, int $id): Response
    {
        $element = $this->entityManager->getRepository($entityName)->find($id);
        if (null === $element) {
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        return $response
            ->withAddedHeader('ETag', md5(serialize($element)))
            ->withJson($element);
    }

    /**
     * Summary: Returns status code 204 if elementname exists
     *
     * @param Response $response
     * @param string $entityName
     * @param string $elementName
     * @return Response
     */
    public function getElementByName(Response $response, string $entityName, string $elementName): Response
    {
        $element = $this->entityManager
            ->getRepository($entityName)
            ->findOneBy([ 'name' => $elementName ]);

        if (null === $element) {
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        return $response
            ->withStatus(StatusCode::STATUS_NO_CONTENT);  // 204
    }

    /**
     * Summary: Deletes a element
     *
     * @param Request $request
     * @param Response $response
     * @param string $entityName
     * @param int $id
     * @return Response
     */
    public function opDelete(Request $request, Response $response, string $entityName, int $id): Response
    {
        if (!$this->checkWriterScope($request)) { // 403
            return Error::error($response, StatusCode::STATUS_FORBIDDEN);
        }

        $product = $this->entityManager->getRepository($entityName)->find($id);

        if (null === $product) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush($product);

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
     * Summary: Creates a new element
     *
     * @param Request $request
     * @param Response $response
     * @param string $entityName
     * @return Response
     */
    public function opPost(Request $request, Response $response, string $entityName): Response
    {
        if (!$this->checkWriterScope($request)) { // 403
            return Error::error($response, StatusCode::STATUS_FORBIDDEN);
        }

        $req_data
            = $request->getParsedBody()
            ?? json_decode($request->getBody(), true);
        $req_data = $req_data ?? [];

        if (!isset($req_data['name'])) { // 422 - Faltan datos
            return Error::error($response, StatusCode::STATUS_UNPROCESSABLE_ENTITY);
        }

        // hay datos -> procesarlos
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria
            ->where($criteria::expr()->eq('name', $req_data['name']));
        // STATUS_BAD_REQUEST 400: element name already exists
        if ($this->entityManager->getRepository($entityName)->matching($criteria)->count()) {
            return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
        }

        // 201
        $element = new $entityName($req_data['name']);
        $this->updateElement($element, $req_data);
        $this->entityManager->persist($element);
        $this->entityManager->flush($element);

        return $response
            ->withAddedHeader(
                'Location',
                $request->getUri()->getPath() . '/' . $element->getId()
            )
            ->withJson($element, StatusCode::STATUS_CREATED);
    }

    /**
     * Summary: Updates a element
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param string $entityName
     * @return Response
     */
    public function opPut(Request $request, Response $response, array $args, string $entityName): Response
    {
        if (!$this->checkWriterScope($request)) { // 403
            return Error::error($response, StatusCode::STATUS_FORBIDDEN);
        }

        $req_data
            = $request->getParsedBody()
            ?? json_decode($request->getBody(), true);
        $req_data = $req_data ?? [];
        // recuperar el elemento
        /** @var Element $element */
        $element = $this->entityManager->getRepository($entityName)->find($args['id']);

        if (null === $element) {    // 404
            return Error::error($response, StatusCode::STATUS_NOT_FOUND);
        }

        if (isset($req_data['name'])) { // 400
            $elementId = $this->findIdBy($entityName, 'name', $req_data['name']);
            if ($elementId && ($args['id'] != $elementId)) {
                // 400 BAD_REQUEST: elementname already exists
                return Error::error($response, StatusCode::STATUS_BAD_REQUEST);
            }
            $element->setName($req_data['name']);
        }

        $this->updateElement($element, $req_data);
        $this->entityManager->flush($element);

        return $response
            ->withStatus(209, 'Content Returned')
            ->withJson($element);
    }

    /**
     * Determines if a value exists for an attribute
     *
     * @param string $entityName
     * @param string $attr attribute
     * @param string $value value
     * @return int
     */
    protected function findIdBy(string $entityName, string $attr, string $value): int
    {
        $element = $this->entityManager->getRepository($entityName)->findOneBy([ $attr => $value ]);
        return $element ? $element->getId() : 0;
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function checkWriterScope(Request $request): bool
    {
        $scopes = $request->getAttribute('token')->getClaim('scopes', null);
        return in_array(Role::ROLE_WRITER, $scopes, true);
    }

    /**
     * Update $element with $data attributes
     *
     * @param Element $element
     * @param array $data
     */
    protected function updateElement(Element $element, array $data): void
    {
        foreach ($data as $attr => $datum) {
            ($attr === 'birthDate')
                ? $element->setBirthDate(DateTime::createFromFormat('!Y-m-d', $datum))
                : null;
            ($attr === 'deathDate')
                ? $element->setDeathDate(DateTime::createFromFormat('!Y-m-d', $datum))
                : null;
            ($attr === 'imageUrl') ? $element->setImageUrl($datum) : null;
            ($attr === 'wikiUrl') ? $element->setWikiUrl($datum) : null;
        }
    }
}
