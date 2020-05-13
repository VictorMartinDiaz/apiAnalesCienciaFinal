<?php

/**
 * PHP version 7.4
 * tests/Controller/Entity/EntityControllerTest.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\Test\ACiencia\Controller\Entity;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use TDW\ACiencia\Utility\Utils;
use TDW\Test\ACiencia\Controller\BaseTestCase;

/**
 * Class EntityControllerTest
 */
class EntityControllerTest extends BaseTestCase
{
    /** @var string Path para la gestión de usuarios */
    protected const RUTA_API = '/api/v1/entities';

    /** @var array Admin data */
    protected static array $writer;

    /** @var array reader user data */
    protected static array $reader;

    /**
     * Se ejecuta una vez al inicio de las pruebas de la clase UserControllerTest
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$reader = [
            'username' => self::$faker->userName,
            'email'    => self::$faker->email,
            'password' => self::$faker->password,
        ];

        // load user admin fixtures
        parent::$writer['id'] = Utils::loadUserData(
            parent::$writer['username'],
            parent::$writer['email'],
            parent::$writer['password'],
            true
        );

        // load user reader fixtures
        self::$reader['id'] = Utils::loadUserData(
            self::$reader['username'],
            self::$reader['email'],
            self::$reader['password'],
            false
        );
    }

    /**
     * Implements testCGetEntities404NotFound
     */
    public function testCGetEntities404NotFound()
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API,
            null,
            $this->getTokenHeaders(self::$reader['username'], self::$reader['password'])
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Implements testPost /entities
     *
     * @depends testCGetEntities404NotFound
     */
    public function testPostEntity201Ok()
    {
        $p_data = [
            'name'      => self::$faker->words(3, true),
            'birthDate' => self::$faker->date(),
            'deathDate' => self::$faker->date(),
            'imageUrl'  => self::$faker->imageUrl(),
            'wikiUrl'   => self::$faker->url
        ];
        $response = $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            $this->getTokenHeaders(parent::$writer['username'], parent::$writer['password'])
        );

        self::assertSame(201, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('Location'));
        self::assertJson((string) $response->getBody());
        $responseEntity = json_decode((string) $response->getBody(), true);
        $entityData = $responseEntity['entity'];
        self::assertNotEquals(0, $entityData['id']);
        self::assertSame($p_data['name'], $entityData['name']);
        self::assertSame($p_data['birthDate'], $entityData['birthDate']);
        self::assertSame($p_data['deathDate'], $entityData['deathDate']);
        self::assertSame($p_data['imageUrl'], $entityData['imageUrl']);
        self::assertSame($p_data['wikiUrl'], $entityData['wikiUrl']);

        return $entityData;
    }

    /**
     * Test POST /users 422
     */
    public function testPostEntity422UnprocessableEntity(): void
    {
        $p_data = [
            // 'name'      => self::$faker->words(3, true),
            'birthDate' => self::$faker->date(),
            'deathDate' => self::$faker->date(),
            'imageUrl'  => self::$faker->imageUrl(),
            'wikiUrl'   => self::$faker->url
        ];
        $response = $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            $this->getTokenHeaders(parent::$writer['username'], parent::$writer['password'])
        );
        $this->internalTestError($response, StatusCode::STATUS_UNPROCESSABLE_ENTITY);
    }
    /**
     * Test POST /entities 400
     *
     * @param array $entity entity returned by testPostEntity201Ok()
     *
     * @depends testPostEntity201Ok
     */
    public function testPostEntity400BadRequest(array $entity): void
    {
        // Mismo username
        $p_data = [
            'name' => $entity['name'],
        ];
        $response = $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_BAD_REQUEST);
    }

    /**
     * Test GET /entities
     *
     * @depends testPostEntity201Ok
     */
    public function testCGetEntities200Ok(): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API,
            null,
            $this->getTokenHeaders()
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('ETag'));
        $r_body = (string) $response->getBody();
        self::assertJson($r_body);
        self::assertStringContainsString('entities', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertArrayHasKey('entities', $r_data);
        self::assertIsArray($r_data['entities']);
    }

    /**
     * Test GET /entities/{entityId}
     *
     * @param array $entity entity returned by testPostEntity201Ok()
     *
     * @depends testPostEntity201Ok
     */
    public function testGetEntity200Ok(array $entity): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $entity['id'],
            null,
            $this->getTokenHeaders()
        );

        self::assertSame(
            200,
            $response->getStatusCode(),
            'Headers: ' . json_encode($this->getTokenHeaders())
        );
        self::assertNotEmpty($response->getHeader('ETag'));
        self::assertJson((string) $response->getBody());
        $entity_aux = json_decode((string) $response->getBody(), true);
        self::assertSame($entity, $entity_aux['entity']);
    }

    /**
     * Test GET /entities/entityname/{entityname} 204 Ok
     *
     * @param array $entity entity returned by testPostEntity201()
     *
     * @depends testPostEntity201Ok
     */
    public function testGetEntityname204NoContent(array $entity): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/entityname/' . $entity['name']
        );

        self::assertSame(
            204,
            $response->getStatusCode()
        );
        self::assertEmpty($response->getBody()->getContents());
    }

    /**
     * Test PUT /entities/{entityId}   209
     *
     * @param array $entity entity returned by testPostEntity201Ok()
     *
     * @depends testPostEntity201Ok
     *
     * @return array modified entity data
     */
    public function testPutEntity209Updated(array $entity): array
    {
        $p_data = [
            'name'  => self::$faker->words(3, true),
            'birthDate' => self::$faker->date(),
            'deathDate' => self::$faker->date(),
            'imageUrl'  => self::$faker->imageUrl(),
            'wikiUrl'   => self::$faker->url
        ];

        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $entity['id'],
            $p_data,
            $this->getTokenHeaders()
        );

        self::assertSame(209, $response->getStatusCode(), 'ERROR: ' . $response->getBody());
        self::assertJson((string) $response->getBody());
        $entity_aux = json_decode((string) $response->getBody(), true);
        self::assertSame($entity['id'], $entity_aux['entity']['id']);
        self::assertSame($p_data['name'], $entity_aux['entity']['name']);
        self::assertSame($p_data['birthDate'], $entity_aux['entity']['birthDate']);
        self::assertSame($p_data['deathDate'], $entity_aux['entity']['deathDate']);
        self::assertSame($p_data['imageUrl'], $entity_aux['entity']['imageUrl']);
        self::assertSame($p_data['wikiUrl'], $entity_aux['entity']['wikiUrl']);

        return $entity_aux['entity'];
    }

    /**
     * Test PUT /entities 400
     *
     * @param array $entity entity returned by testPutEntity209Updated()
     *
     * @depends testPutEntity209Updated
     */
    public function testPutEntity400BadRequest(array $entity): void
    {
        $p_data = [ 'name' => self::$faker->words(3, true) ];
        $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            $this->getTokenHeaders(parent::$writer['username'], parent::$writer['password'])
        );

        // entityname already exists
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $entity['id'],
            $p_data,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_BAD_REQUEST);
    }

    /**
     * Test OPTIONS /entities[/{entityId}]
     */
    public function testOptionsEntity200Ok(): void
    {
        $response = $this->runApp(
            'OPTIONS',
            self::RUTA_API
        );
        self::assertSame(200, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('Allow'));
        self::assertEmpty($response->getBody()->getContents());

        $response = $this->runApp(
            'OPTIONS',
            self::RUTA_API . '/' . self::$faker->randomDigitNotNull
        );
        self::assertSame(200, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('Allow'));
        self::assertEmpty($response->getBody()->getContents());
    }

    /**
     * Test DELETE /entities/{entityId}
     *
     * @param array $entity entity returned by testPostEntity201Ok()
     *
     * @depends testPostEntity201Ok
     * @depends testPostEntity400BadRequest
     * @depends testGetEntity200Ok
     * @depends testPostEntity422UnprocessableEntity
     * @depends testGetEntityname204NoContent
     *
     * @return int entityId
     */
    public function testDeleteEntity204NoContent(array $entity): int
    {
        $response = $this->runApp(
            'DELETE',
            self::RUTA_API . '/' . $entity['id'],
            null,
            $this->getTokenHeaders()
        );

        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty((string) $response->getBody());
        self::assertEmpty($response->getBody()->getContents());

        return $entity['id'];
    }


    /**
     * Test GET /entities/entityname/{entityname} 404 Not Found
     *
     * @param array $entity entity returned by testPutEntity209Updated()
     *
     * @depends testPutEntity209Updated
     * @depends testDeleteEntity204NoContent
     */
    public function testGetEntityname404NotFound(array $entity): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/entityname/' . $entity['name']
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test DELETE /entities/{entityId} 404 Not Found
     *
     * @param int $entityId entity id. returned by testDeleteEntity204NoContent()
     *
     * @depends testDeleteEntity204NoContent
     */
    public function testDeleteEntity404NotFound(int $entityId): void
    {
        $response = $this->runApp(
            'DELETE',
            self::RUTA_API . '/' . $entityId,
            null,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET /entities/{entityId} 404 Not Found
     *
     * @param int $entityId entity id. returned by testDeleteEntity204NoContent()
     *
     * @depends testDeleteEntity204NoContent
     */
    public function testGetEntity404NotFound(int $entityId): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $entityId,
            null,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test PUT /entities/{entityId} 404 Not Found
     *
     * @param int $entityId entity id. returned by testDeleteEntity204NoContent()
     *
     * @depends testDeleteEntity204NoContent
     */
    public function testPutEntity404NotFound(int $entityId): void
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $entityId,
            null,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET    /entities 401 UNAUTHORIZED
     * Test POST   /entities 401 UNAUTHORIZED
     * Test GET    /entities/{entityId} 401 UNAUTHORIZED
     * Test PUT    /entities/{entityId} 401 UNAUTHORIZED
     * Test DELETE /entities/{entityId} 401 UNAUTHORIZED
     *
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider401()
     * @return void
     */
    public function testEntitiestatus401Unauthorized(string $method, string $uri): void
    {
        $response = $this->runApp(
            $method,
            $uri
        );
        $this->internalTestError($response, StatusCode::STATUS_UNAUTHORIZED);
    }

    /**
     * Test POST   /entities 403 FORBIDDEN
     * Test PUT    /entities/{entityId} 403 FORBIDDEN
     * Test DELETE /entities/{entityId} 403 FORBIDDEN
     *
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider403()
     * @return void
     */
    public function testEntitiestatus403Forbidden(string $method, string $uri): void
    {
        $response = $this->runApp(
            $method,
            $uri,
            null,
            $this->getTokenHeaders(self::$reader['username'], self::$reader['password'])
        );
        $this->internalTestError($response, StatusCode::STATUS_FORBIDDEN);
    }

    // --------------
    // DATA PROVIDERS
    // --------------

    /**
     * Route provider (expected status: 401 UNAUTHORIZED)
     *
     * @return array [ method, url ]
     */
    public function routeProvider401(): array
    {
        return [
            'cgetAction401'   => [ 'GET',    self::RUTA_API ],
            'getAction401'    => [ 'GET',    self::RUTA_API . '/1' ],
            'postAction401'   => [ 'POST',   self::RUTA_API ],
            'putAction401'    => [ 'PUT',    self::RUTA_API . '/1' ],
            'deleteAction401' => [ 'DELETE', self::RUTA_API . '/1' ],
        ];
    }

    /**
     * Route provider (expected status: 403 FORBIDDEN)
     *
     * @return array [ method, url ]
     */
    public function routeProvider403(): array
    {
        return [
            'postAction403'   => [ 'POST',   self::RUTA_API ],
            'putAction403'    => [ 'PUT',    self::RUTA_API . '/1' ],
            'deleteAction403' => [ 'DELETE', self::RUTA_API . '/1' ],
        ];
    }
}
