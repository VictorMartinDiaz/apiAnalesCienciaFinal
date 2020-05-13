<?php

/**
 * PHP version 7.4
 * config/routesEntities.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use Slim\App;
use TDW\ACiencia\Controller\Entity\EntityController;
use TDW\ACiencia\Controller\Entity\EntityRelationsController;
use TDW\ACiencia\Middleware\JwtMiddleware;

/**
 * ############################################################
 * routes /api/v1/entities
 * ############################################################
 * @param App $app
 */
return function (App $app) {

    $REGEX_ENTITY_ID = '/{entityId:[0-9]+}';
    $REGEX_STUFF_ID = '/{stuffId:[0-9]+}';
    $REGEX_ENTITY_NAME = '[a-zA-Z0-9()áéíóúÁÉÍÓÚñÑ %$\.+-]+';

    // CGET: Returns all entities
    $app->get(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES,
        EntityController::class . ':cget'
    )->setName('tdw_cget_entities')
        ->add(JwtMiddleware::class);

    // GET: Returns a entity based on a single ID
    $app->get(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES . $REGEX_ENTITY_ID,
        EntityController::class . ':get'
    )->setName('tdw_get_entities')
        ->add(JwtMiddleware::class);

    // GET: Returns status code 204 if entityname exists
    $app->get(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES . '/entityname/{entityname:' . $REGEX_ENTITY_NAME . '}',
        EntityController::class . ':getEntityname'
    )->setName('tdw_get_entity_name');

    // DELETE: Deletes a entity
    $app->delete(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES . $REGEX_ENTITY_ID,
        EntityController::class . ':delete'
    )->setName('tdw_delete_entities')
        ->add(JwtMiddleware::class);

    // OPTIONS: Provides the list of HTTP supported methods
    $app->options(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES . '[' . $REGEX_ENTITY_ID . ']',
        EntityController::class . ':options'
    )->setName('tdw_options_entities');

    // POST: Creates a new product
    $app->post(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES,
        EntityController::class . ':post'
    )->setName('tdw_post_entities')
        ->add(JwtMiddleware::class);

    // PUT: Updates a product
    $app->put(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES . $REGEX_ENTITY_ID,
        EntityController::class . ':put'
    )->setName('tdw_put_entities')
        ->add(JwtMiddleware::class);

    // RELATIONSHIPS

    // GET /entities/{entityId}/persons
    $app->get(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES . $REGEX_ENTITY_ID . '/persons',
        EntityRelationsController::class . ':getPersons'
    )->setName('tdw_entities_get_persons')
        ->add(JwtMiddleware::class);

    // PUT /entities/{entityId}/persons/add/{stuffId}
    $app->put(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES . $REGEX_ENTITY_ID . '/persons/add' . $REGEX_STUFF_ID,
        EntityRelationsController::class . ':operationPerson'
    )->setName('tdw_entities_add_person')
        ->add(JwtMiddleware::class);

    // PUT /entities/{entityId}/persons/rem/{stuffId}
    $app->put(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES . $REGEX_ENTITY_ID . '/persons/rem' . $REGEX_STUFF_ID,
        EntityRelationsController::class . ':operationPerson'
    )->setName('tdw_entities_rem_person')
        ->add(JwtMiddleware::class);

    // GET /entities/{entityId}/products
    $app->get(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES . $REGEX_ENTITY_ID . '/products',
        EntityRelationsController::class . ':getProducts'
    )->setName('tdw_entities_get_products')
        ->add(JwtMiddleware::class);

    // PUT /entities/{entityId}/products/add/{stuffId}
    $app->put(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES . $REGEX_ENTITY_ID
            . '/products/add' . $REGEX_STUFF_ID,
        EntityRelationsController::class . ':operationProduct'
    )->setName('tdw_entities_add_product')
        ->add(JwtMiddleware::class);

    // PUT /entities/{entityId}/products/rem/{stuffId}
    $app->put(
        $_ENV['RUTA_API'] . EntityController::PATH_ENTITIES . $REGEX_ENTITY_ID
        . '/products/rem' . $REGEX_STUFF_ID,
        EntityRelationsController::class . ':operationProduct'
    )->setName('tdw_entities_rem_product')
        ->add(JwtMiddleware::class);
};
