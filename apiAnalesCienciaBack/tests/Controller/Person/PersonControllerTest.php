<?php

/**
 * PHP version 7.4
 * tests/Controller/Person/PersonControllerTest.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\Test\ACiencia\Controller\Person;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use TDW\ACiencia\Utility\Utils;
use TDW\Test\ACiencia\Controller\BaseTestCase;

/**
 * Class PersonControllerTest
 */
class PersonControllerTest extends BaseTestCase
{
    /** @var string Path para la gestión de usuarios */
    protected const RUTA_API = '/api/v1/persons';

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
     * Implements testCGetPersons404NotFound
     */
    public function testCGetPersons404NotFound()
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
     * Implements testPost /persons
     *
     * @depends testCGetPersons404NotFound
     */
    public function testPostPerson201Ok()
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
        $responsePerson = json_decode((string) $response->getBody(), true);
        $personData = $responsePerson['person'];
        self::assertNotEquals(0, $personData['id']);
        self::assertSame($p_data['name'], $personData['name']);
        self::assertSame($p_data['birthDate'], $personData['birthDate']);
        self::assertSame($p_data['deathDate'], $personData['deathDate']);
        self::assertSame($p_data['imageUrl'], $personData['imageUrl']);
        self::assertSame($p_data['wikiUrl'], $personData['wikiUrl']);

        return $personData;
    }

    /**
     * Test POST /users 422
     */
    public function testPostPerson422UnprocessableEntity(): void
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
     * Test POST /persons 400
     *
     * @param array $person person returned by testPostPerson201Ok()
     *
     * @depends testPostPerson201Ok
     */
    public function testPostPerson400BadRequest(array $person): void
    {
        // Mismo username
        $p_data = [
            'name' => $person['name'],
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
     * Test GET /persons
     *
     * @depends testPostPerson201Ok
     */
    public function testCGetPersons200Ok(): void
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
        self::assertStringContainsString('persons', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertArrayHasKey('persons', $r_data);
        self::assertIsArray($r_data['persons']);
    }

    /**
     * Test GET /persons/{personId}
     *
     * @param array $person person returned by testPostPerson201Ok()
     *
     * @depends testPostPerson201Ok
     */
    public function testGetPerson200Ok(array $person): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $person['id'],
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
        $person_aux = json_decode((string) $response->getBody(), true);
        self::assertSame($person, $person_aux['person']);
    }

    /**
     * Test GET /persons/personname/{personname} 204 Ok
     *
     * @param array $person person returned by testPostPerson201()
     *
     * @depends testPostPerson201Ok
     */
    public function testGetPersonname204NoContent(array $person): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/personname/' . $person['name']
        );

        self::assertSame(
            204,
            $response->getStatusCode()
        );
        self::assertEmpty($response->getBody()->getContents());
    }

    /**
     * Test PUT /persons/{personId}   209
     *
     * @param array $person person returned by testPostPerson201Ok()
     *
     * @depends testPostPerson201Ok
     *
     * @return array modified person data
     */
    public function testPutPerson209Updated(array $person): array
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
            self::RUTA_API . '/' . $person['id'],
            $p_data,
            $this->getTokenHeaders()
        );

        self::assertSame(209, $response->getStatusCode(), 'ERROR: ' . $response->getBody());
        self::assertJson((string) $response->getBody());
        $person_aux = json_decode((string) $response->getBody(), true);
        self::assertSame($person['id'], $person_aux['person']['id']);
        self::assertSame($p_data['name'], $person_aux['person']['name']);
        self::assertSame($p_data['birthDate'], $person_aux['person']['birthDate']);
        self::assertSame($p_data['deathDate'], $person_aux['person']['deathDate']);
        self::assertSame($p_data['imageUrl'], $person_aux['person']['imageUrl']);
        self::assertSame($p_data['wikiUrl'], $person_aux['person']['wikiUrl']);

        return $person_aux['person'];
    }

    /**
     * Test PUT /persons 400
     *
     * @param array $person person returned by testPutPerson209Updated()
     *
     * @depends testPutPerson209Updated
     */
    public function testPutPerson400BadRequest(array $person): void
    {
        $p_data = [ 'name' => self::$faker->words(3, true) ];
        $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            $this->getTokenHeaders(parent::$writer['username'], parent::$writer['password'])
        );

        // personname already exists
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $person['id'],
            $p_data,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_BAD_REQUEST);
    }

    /**
     * Test OPTIONS /persons[/{personId}]
     */
    public function testOptionsPerson200Ok(): void
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
     * Test DELETE /persons/{personId}
     *
     * @param array $person person returned by testPostPerson201Ok()
     *
     * @depends testPostPerson201Ok
     * @depends testPostPerson400BadRequest
     * @depends testGetPerson200Ok
     * @depends testPostPerson422UnprocessableEntity
     * @depends testGetPersonname204NoContent
     *
     * @return int personId
     */
    public function testDeletePerson204NoContent(array $person): int
    {
        $response = $this->runApp(
            'DELETE',
            self::RUTA_API . '/' . $person['id'],
            null,
            $this->getTokenHeaders()
        );

        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty((string) $response->getBody());
        self::assertEmpty($response->getBody()->getContents());

        return $person['id'];
    }

    /**
     * Test GET /persons/personname/{personname} 404 Not Found
     *
     * @param array $person person returned by testPutPerson209Updated()
     *
     * @depends testPutPerson209Updated
     * @depends testDeletePerson204NoContent
     */
    public function testGetPersonname404NotFound(array $person): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/personname/' . $person['name']
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test DELETE /persons/{personId} 404 Not Found
     *
     * @param int $personId person id. returned by testDeletePerson204NoContent()
     *
     * @depends testDeletePerson204NoContent
     */
    public function testDeletePerson404NotFound(int $personId): void
    {
        $response = $this->runApp(
            'DELETE',
            self::RUTA_API . '/' . $personId,
            null,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET /persons/{personId} 404 Not Found
     *
     * @param int $personId person id. returned by testDeletePerson204NoContent()
     *
     * @depends testDeletePerson204NoContent
     */
    public function testGetPerson404NotFound(int $personId): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $personId,
            null,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test PUT /persons/{personId} 404 Not Found
     *
     * @param int $personId person id. returned by testDeletePerson204NoContent()
     *
     * @depends testDeletePerson204NoContent
     */
    public function testPutPerson404NotFound(int $personId): void
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $personId,
            null,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET    /persons 401 UNAUTHORIZED
     * Test POST   /persons 401 UNAUTHORIZED
     * Test GET    /persons/{personId} 401 UNAUTHORIZED
     * Test PUT    /persons/{personId} 401 UNAUTHORIZED
     * Test DELETE /persons/{personId} 401 UNAUTHORIZED
     *
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider401()
     * @return void
     */
    public function testPersonStatus401Unauthorized(string $method, string $uri): void
    {
        $response = $this->runApp(
            $method,
            $uri
        );
        $this->internalTestError($response, StatusCode::STATUS_UNAUTHORIZED);
    }

    /**
     * Test POST   /persons 403 FORBIDDEN
     * Test PUT    /persons/{personId} 403 FORBIDDEN
     * Test DELETE /persons/{personId} 403 FORBIDDEN
     *
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider403()
     * @return void
     */
    public function testPersonStatus403Forbidden(string $method, string $uri): void
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
