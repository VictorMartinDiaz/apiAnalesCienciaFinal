<?php

/**
 * PHP version 7.4
 * tests/Controller/BaseTestCase.php
 */

namespace TDW\Test\ACiencia\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Environment;
use Slim\Http\Factory\DecoratedServerRequestFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface as Response;
use TDW\ACiencia\Utility\Error;
use TDW\ACiencia\Utility\Utils;
use Throwable;

/**
 * This is an example class that shows how you could set up a method that
 * runs the application. Note that it doesn't cover all use-cases and is
 * tuned to the specifics of this skeleton app, so if your needs are
 * different, you'll need to change it.
 */
class BaseTestCase extends TestCase
{
    /** @var array $writer Admin User */
    protected static array $writer = [];

    protected static array $headers;

    protected static \Faker\Generator $faker;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        self::$faker = \Faker\Factory::create('es_ES');
        self::$writer = [
            'username' => getenv('ADMIN_USER_NAME'),
            'email'    => getenv('ADMIN_USER_EMAIL'),
            'password' => getenv('ADMIN_USER_PASSWD'),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        Utils::updateSchema(); // Deja las tablas vacías
    }

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|null $requestData the request data
     * @param array|null $requestHeaders the request headers
     *
     * @return Response
     */
    public function runApp(
        string $requestMethod,
        string $requestUri,
        array $requestData = null,
        array $requestHeaders = null
    ): Response {

        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD'     => $requestMethod,
                'REQUEST_URI'        => $requestUri,
                'HTTP_AUTHORIZATION' => $requestHeaders['Authorization'] ?? null,
            ]
        );

        // Set up a request object based on the environment
        $factory = new DecoratedServerRequestFactory(new ServerRequestFactory());
        $request = $factory->createServerRequest(
            $requestMethod,
            $requestUri,
            $environment
        );

        // Add request data, if it exists
        if (null !== $requestData) {
            $request = $request->withParsedBody($requestData);
        }

        // Add request headers, if it exists
        if (null !== $requestHeaders) {
            foreach ($requestHeaders as $header_name => $value) {
                $request = clone $request->withAddedHeader($header_name, $value);
            }
        }

        // Instantiate the application
        /** @var App $app */
        $app = (require __DIR__ . '/../../config/bootstrap.php');

        // Process the application
        try {
            $response = $app->handle($request);
        } catch (Throwable $exception) {
            die('ERROR: ' . $exception->getMessage());
        }

        // Return the response
        return $response;
    }

    /**
     * Obtiene las cabeceras de la petición de la ruta correspondiente
     * Si recibe como parámetro un nombre de usuario, genera un nuevo token
     * Sino, si anteriormente existe el token, lo reenvía
     *
     * @param string $username user name
     * @param string $password user password
     *
     * @return array cabeceras con el token obtenido
     */
    protected function getTokenHeaders(
        string $username = null,
        string $password = null
    ): array {
        if (empty(self::$headers) || null !== $username) {
            $data = [
                'username' => $username ?? self::$writer['username'],
                'password' => $password ?? self::$writer['password']
            ];
            $response = $this->runApp(
                'POST',
                $_ENV['RUTA_LOGIN'],
                $data
            );
            self::$headers = [ 'Authorization' => $response->getHeaderLine('Authorization') ];
        }

        return self::$headers;
    }

    /**
     * Test error messages
     *
     * @param ResponseInterface $response
     * @param int $errorCode
     */
    protected function internalTestError(ResponseInterface $response, int $errorCode): void
    {
        self::assertSame($errorCode, $response->getStatusCode());
        $r_body = (string) $response->getBody();
        self::assertJson($r_body);
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        try {
            $r_data = json_decode($r_body, true, 512, JSON_THROW_ON_ERROR);
            self::assertSame($errorCode, $r_data['code']);
            self::assertSame(Error::MESSAGES[$errorCode], $r_data['message']);
        } catch (Throwable $e) {
        }
    }
}
