<?php

/**
 * PHP version 7.4
 * tests/Controller/UserControllerTest.php
 */

namespace TDW\Test\ACiencia\Controller;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use TDW\ACiencia\Entity\Role;
use TDW\ACiencia\Utility\Utils;

/**
 * Class UserControllerTest
 */
class UserControllerTest extends BaseTestCase
{
    /** @var string Path para la gestión de usuarios */
    private const RUTA_API = '/api/v1/users';

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
        self::$writer = [
            'username' => getenv('ADMIN_USER_NAME'),
            'email'    => getenv('ADMIN_USER_EMAIL'),
            'password' => getenv('ADMIN_USER_PASSWD'),
        ];

        self::$reader = [
            'username' => self::$faker->userName,
            'email'    => self::$faker->email,
            'password' => self::$faker->password,
        ];

        // load user admin fixtures
        self::$writer['id'] = Utils::loadUserData(
            self::$writer['username'],
            self::$writer['email'],
            self::$writer['password'],
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
     * Test POST /users
     *
     * @return array user data
     */
    public function testPostUser201Created(): array
    {
        $p_data = [
            'username'  => self::$faker->userName,
            'email'     => self::$faker->email,
            'password'  => self::$faker->password,
            'role'      => Role::ROLES[self::$faker->numberBetween(0, 1)],
        ];
        $response = $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            $this->getTokenHeaders(self::$writer['username'], self::$writer['password'])
        );

        self::assertSame(201, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('Location'));
        self::assertJson((string) $response->getBody());
        $responseUser = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $userData = $responseUser['user'];
        self::assertNotEquals($userData['id'], 0);
        self::assertSame($p_data['username'], $userData['username']);
        self::assertSame($p_data['email'], $userData['email']);
        self::assertEquals($p_data['role'], $userData['role']);

        return $userData;
    }

    /**
     * Test POST /users 422
     * @param string $username
     * @param string $email
     * @param string $password
     *
     * @dataProvider dataProviderPostUser422
     */
    public function testPostUser422UnprocessableEntity(?string $username, ?string $email, ?string $password): void
    {
        $p_data = [
            'username' => $username,
            'email'    => $email,
            'password' => $password,
        ];
        $response = $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_UNPROCESSABLE_ENTITY);
    }

    /**
     * Test POST /users 400
     *
     * @param array $user user returned by testPostUser201()
     *
     * @depends testPostUser201Created
     */
    public function testPostUser400BadRequest(array $user): void
    {
        // Mismo username
        $p_data = [
            'username' => $user['username'],
            'email'    => self::$faker->email,
            'password' => self::$faker->password
        ];
        $response = $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_BAD_REQUEST);

        // Mismo email
        $p_data = [
            'username' => self::$faker->userName,
            'email'    => $user['email'],
            'password' => self::$faker->password
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
     * Test GET /users
     *
     * @depends testPostUser201Created
     */
    public function testCGetAllUsers200Ok(): void
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
        self::assertStringContainsString('users', $r_body);
        $r_data = json_decode($r_body, true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('users', $r_data);
        self::assertIsArray($r_data['users']);
    }

    /**
     * Test GET /users/userId
     *
     * @param array $user user returned by testPostUser201()
     *
     * @depends testPostUser201Created
     */
    public function testGetUser200Ok(array $user): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $user['id'],
            null,
            $this->getTokenHeaders()
        );

        self::assertSame(
            200,
            $response->getStatusCode(),
            'Headers: ' . json_encode($this->getTokenHeaders(), JSON_THROW_ON_ERROR)
        );
        self::assertNotEmpty($response->getHeader('ETag'));
        self::assertJson((string) $response->getBody());
        $user_aux = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($user, $user_aux['user']);
    }

    /**
     * Test GET /users/username/{username} 204 Ok
     *
     * @param array $user user returned by testPostUser201()
     *
     * @depends testPostUser201Created
     */
    public function testGetUsername204NoContent(array $user): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/username/' . $user['username']
        );

        self::assertSame(
            204,
            $response->getStatusCode(),
            'Headers: ' . json_encode($this->getTokenHeaders(), JSON_THROW_ON_ERROR)
        );
        self::assertEmpty($response->getBody()->getContents());
    }

    /**
     * Test PUT /users/userId   209
     *
     * @param array $user user returned by testPostUser201()
     *
     * @depends testPostUser201Created
     *
     * @return array modified user data
     */
    public function testPutUser209Updated(array $user): array
    {
        $p_data = [
            'username'  => self::$faker->userName,
            'email'     => self::$faker->email,
            'password'  => self::$faker->password,
            'role'      => Role::ROLES[self::$faker->numberBetween(0, 1)],
        ];

        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $user['id'],
            $p_data,
            $this->getTokenHeaders()
        );

        self::assertSame(209, $response->getStatusCode(), 'ERROR: ' . $response->getBody());
        self::assertJson((string) $response->getBody());
        $user_aux = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($user['id'], $user_aux['user']['id']);
        self::assertSame($p_data['username'], $user_aux['user']['username']);
        self::assertSame($p_data['email'], $user_aux['user']['email']);
        self::assertEquals($p_data['role'], $user_aux['user']['role']);

        return $user_aux['user'];
    }

    /**
     * Test PUT /users 400
     *
     * @param array $user user returned by testPutUser200()
     *
     * @depends testPutUser209Updated
     */
    public function testPutUser400BadRequest(array $user): void
    {
        $p_data = [
                ['username' => self::$writer['username']],   // username already exists
                ['email' => self::$writer['email']],         // e-mail already exists
                ['role' => self::$faker->word],             // role out of range
            ];
        foreach ($p_data as $pair) {
            $response = $this->runApp(
                'PUT',
                self::RUTA_API . '/' . $user['id'],
                $pair,
                $this->getTokenHeaders()
            );
            $this->internalTestError($response, StatusCode::STATUS_BAD_REQUEST);
        }
    }

    /**
     * Test OPTIONS /users[/userId]
     */
    public function testOptionsUser200Ok(): void
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
     * Test DELETE /users/userId
     *
     * @param array $user user returned by testPostUser201()
     *
     * @depends testPostUser201Created
     * @depends testPostUser400BadRequest
     * @depends testGetUser200Ok
     * @depends testPutUser400BadRequest
     * @depends testGetUsername204NoContent
     *
     * @return int userId
     */
    public function testDeleteUser204NoContent(array $user): int
    {
        $response = $this->runApp(
            'DELETE',
            self::RUTA_API . '/' . $user['id'],
            null,
            $this->getTokenHeaders()
        );

        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty((string) $response->getBody());
        self::assertEmpty($response->getBody()->getContents());

        return $user['id'];
    }

    /**
     * Test GET /users/username/{username} 404 Not Found
     *
     * @param array $user user returned by testPutUser209Updated()
     *
     * @depends testPutUser209Updated
     * @depends testDeleteUser204NoContent
     */
    public function testGetUsername404NotFound(array $user): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/username/' . \urlencode($user['username'])
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test DELETE /users/userId 404 Not Found
     *
     * @param int $userId user id. returned by testDeleteUser204()
     *
     * @depends testDeleteUser204NoContent
     */
    public function testDeleteUser404NotFound(int $userId): void
    {
        $response = $this->runApp(
            'DELETE',
            self::RUTA_API . '/' . $userId,
            null,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET /users/userId 404 Not Found
     *
     * @param int $userId user id. returned by testDeleteUser204()
     *
     * @depends testDeleteUser204NoContent
     */
    public function testGetUser404NotFound(int $userId): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $userId,
            null,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test PUT /users/userId 404 Not Found
     *
     * @param int $userId user id. returned by testDeleteUser204()
     *
     * @depends testDeleteUser204NoContent
     */
    public function testPutUser404NotFound(int $userId): void
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $userId,
            null,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET    /users 401 UNAUTHORIZED
     * Test POST   /users 401 UNAUTHORIZED
     * Test GET    /users/{userId} 401 UNAUTHORIZED
     * Test PUT    /users/{userId} 401 UNAUTHORIZED
     * Test DELETE /users/{userId} 401 UNAUTHORIZED
     *
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider401()
     * @return void
     */
    public function testUserStatus401Unauthorized(string $method, string $uri): void
    {
        $response = $this->runApp(
            $method,
            $uri
        );
        $this->internalTestError($response, StatusCode::STATUS_UNAUTHORIZED);
    }

    /**
     * Test POST   /users 403 FORBIDDEN
     * Test PUT    /users/{userId} 403 FORBIDDEN
     * Test DELETE /users/{userId} 403 FORBIDDEN
     *
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider403()
     * @return void
     */
    public function testUserStatus403Forbidden(string $method, string $uri): void
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

    public function dataProviderPostUser422(): array
    {
        $faker = \Faker\Factory::create('es_ES');
        $fakeUsername = $faker->userName;
        $fakeEmail = $faker->email;
        $fakePasswd = $faker->password;

        return [
            'empty_data'  => [ null, null, null ],
            'no_username' => [ null, $fakeEmail, $fakePasswd ],
            'no_email'    => [ $fakeUsername, null, $fakePasswd ],
            'no_passwd'   => [ $fakeUsername, $fakeEmail, null ],
            'no_us_pa'    => [ null, $fakeEmail, null ],
            'no_em_pa'    => [ $fakeUsername, null, null ],
        ];
    }

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
