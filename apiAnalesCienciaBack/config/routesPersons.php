<?php

/**
 * PHP version 7.4
 * config/routesPersons.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

use Slim\App;
use TDW\ACiencia\Controller\Person\PersonController;
use TDW\ACiencia\Controller\Person\PersonRelationsController;
use TDW\ACiencia\Middleware\JwtMiddleware;

/**
 * ############################################################
 * routes /api/v1/products
 * ############################################################
 * @param App $app
 */
return function (App $app) {

    $REGEX_PERSON_ID = '/{personId:[0-9]+}';
    $REGEX_STUFF_ID = '/{stuffId:[0-9]+}';
    $REGEX_PERSON_NAME = '[a-zA-Z0-9()áéíóúÁÉÍÓÚñÑ %$\.+-]+';

    // CGET: Returns all persons
    $app->get(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS,
        PersonController::class . ':cget'
    )->setName('tdw_cget_persons')
        ->add(JwtMiddleware::class);

    // GET: Returns a person based on a single ID
    $app->get(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS . $REGEX_PERSON_ID,
        PersonController::class . ':get'
    )->setName('tdw_get_persons')
        ->add(JwtMiddleware::class);

    // GET: Returns status code 204 if personname exists
    $app->get(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS . '/personname/{personname:' . $REGEX_PERSON_NAME . '}',
        PersonController::class . ':getPersonname'
    )->setName('tdw_get_person_name');

    // DELETE: Deletes a person
    $app->delete(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS . $REGEX_PERSON_ID,
        PersonController::class . ':delete'
    )->setName('tdw_delete_persons')
        ->add(JwtMiddleware::class);

    // OPTIONS: Provides the list of HTTP supported methods
    $app->options(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS . '[' . $REGEX_PERSON_ID . ']',
        PersonController::class . ':options'
    )->setName('tdw_options_persons');

    // POST: Creates a new product
    $app->post(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS,
        PersonController::class . ':post'
    )->setName('tdw_post_persons')
        ->add(JwtMiddleware::class);

    // PUT: Updates a product
    $app->put(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS . $REGEX_PERSON_ID,
        PersonController::class . ':put'
    )->setName('tdw_put_persons')
        ->add(JwtMiddleware::class);

    // RELATIONSHIPS

    // GET /persons/{personId}/entities
    $app->get(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS . $REGEX_PERSON_ID . '/entities',
        PersonRelationsController::class . ':getEntities'
    )->setName('tdw_persons_get_entities')
        ->add(JwtMiddleware::class);

    // PUT /persons/{personId}/entities/add/{stuffId}
    $app->put(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS . $REGEX_PERSON_ID
            . '/entities/add' . $REGEX_STUFF_ID,
        PersonRelationsController::class . ':operationEntity'
    )->setName('tdw_persons_add_entity')
        ->add(JwtMiddleware::class);

    // PUT /persons/{personId}/entities/rem/{stuffId}
    $app->put(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS . $REGEX_PERSON_ID
            . '/entities/rem' . $REGEX_STUFF_ID,
        PersonRelationsController::class . ':operationEntity'
    )->setName('tdw_persons_rem_entity')
        ->add(JwtMiddleware::class);

    // GET /persons/{personId}/products
    $app->get(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS . $REGEX_PERSON_ID . '/products',
        PersonRelationsController::class . ':getProducts'
    )->setName('tdw_persons_get_products')
        ->add(JwtMiddleware::class);

    // PUT /persons/{personId}/products/add/{stuffId}
    $app->put(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS . $REGEX_PERSON_ID
            . '/products/add' . $REGEX_STUFF_ID,
        PersonRelationsController::class . ':operationProduct'
    )->setName('tdw_persons_add_product')
        ->add(JwtMiddleware::class);

    // PUT /persons/{personId}/products/rem/{stuffId}
    $app->put(
        $_ENV['RUTA_API'] . PersonController::PATH_PERSONS . $REGEX_PERSON_ID
            . '/products/rem' . $REGEX_STUFF_ID,
        PersonRelationsController::class . ':operationProduct'
    )->setName('tdw_persons_rem_product')
        ->add(JwtMiddleware::class);
};
