<?php

// Define app routes

use TDW\ACiencia\Controller\LoginController;
use Slim\App;

return function (App $app) {

    // Redirection / -> /api-docs/index.html
    $app->redirect(
        '/',
        '/api-docs/index.html'
    )->setName('tdw_home_redirect');

    /**
     * ############################################################
     * routes /access_token
     * POST /access_token
     * ############################################################
     */
    $app->post(
        $_ENV['RUTA_LOGIN'],
        LoginController::class . ':post'
    )->setName('tdw_post_login');
};
