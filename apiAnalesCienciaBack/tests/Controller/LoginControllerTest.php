<?php

/**
 * PHP version 7.4
 * tests/Controller/LoginControllerTest.php
 */

namespace TDW\Test\ACiencia\Controller;

use Faker\Factory;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use TDW\ACiencia\Utility\Error;
use TDW\ACiencia\Utility\Utils;
use Throwable;

/**
 * Class LoginControllerTest
 */
class LoginControllerTest extends BaseTestCase
{
    /** @var string path de login */
    private static $ruta_base;

    protected static array $writer;

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        self::$ruta_base = $_ENV['RUTA_LOGIN'];

        $faker = Factory::create('es_ES');
        self::$writer = [
            'username' => $faker->userName,
            'email'    => $faker->email,
            'password' => $faker->password,
        ];

        // load user admin fixtures
        parent::$writer['id'] = Utils::loadUserData(
            self::$writer['username'],
            self::$writer['email'],
            self::$writer['password'],
            true
        );
    }

    /**
     * Called after the last test of the test case class is run
     */
    public static function tearDownAfterClass(): void
    {
        Utils::updateSchema();
    }

    /**
     * Test POST /login 404 NOT FOUND
     * @param array $data
     * @dataProvider proveedorUsuarios()
     */
    public function testPostLogin404(array $data): void
    {
        $response = $this->runApp(
            'POST',
            self::$ruta_base,
            $data
        );

        self::assertSame(StatusCode::STATUS_NOT_FOUND, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $r_body = (string) $response->getBody();
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertSame(StatusCode::STATUS_NOT_FOUND, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::STATUS_NOT_FOUND],
            $r_data['message']
        );
    }

    /**
     * Test POST /login 200 OK
     */
    public function testPostLogin200(): void
    {
        $data = [
            'username' => self::$writer['username'],
            'password' => self::$writer['password']
        ];
        $response = $this->runApp(
            'POST',
            self::$ruta_base,
            $data
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $r_body = (string) $response->getBody();
        self::assertTrue($response->hasHeader('Authorization'));
        $r_data = json_decode($r_body, true);
        self::assertNotEmpty($r_data['access_token']);
    }

    // --------------
    // DATA PROVIDERS
    // --------------

    public function proveedorUsuarios(): array
    {
        $faker = Factory::create('es_ES');
        $fakeUsername = $faker->userName;
        $fakePasswd = $faker->password;

        try {
            return [
                'empty_user'  => [
                    [ ]
                ],
                'no_password' => [
                    [ 'username' => $fakeUsername ]
                ],
                'no_username' => [
                    [ 'password' => $fakePasswd ]
                ],
                'incorrect_username' => [
                    [ 'username' => $fakeUsername, 'password' => $fakePasswd ]
                ],
                'incorrect_passwd' => [
                    [ 'username' => $fakeUsername, 'password' => $fakePasswd ]
                ],
            ];
        } catch (Throwable $e) {
            die('ERROR: ' . $e->getMessage());
        }
    }
}
