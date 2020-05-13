<?php

/**
 * PHP version 7.4
 * tests/Controller/Product/ProductControllerTest.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\Test\ACiencia\Controller\Product;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use TDW\ACiencia\Utility\Utils;
use TDW\Test\ACiencia\Controller\BaseTestCase;

/**
 * Class ProductControllerTest
 */
class ProductControllerTest extends BaseTestCase
{
    /** @var string Path para la gestión de usuarios */
    protected const RUTA_API = '/api/v1/products';

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
     * Implements testCGetProducts404NotFound
     */
    public function testCGetProducts404NotFound()
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
     * Implements testPost /products
     *
     * @depends testCGetProducts404NotFound
     */
    public function testPostProduct201Ok()
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
        $responseProduct = json_decode((string) $response->getBody(), true);
        $productData = $responseProduct['product'];
        self::assertNotEquals(0, $productData['id']);
        self::assertSame($p_data['name'], $productData['name']);
        self::assertSame($p_data['birthDate'], $productData['birthDate']);
        self::assertSame($p_data['deathDate'], $productData['deathDate']);
        self::assertSame($p_data['imageUrl'], $productData['imageUrl']);
        self::assertSame($p_data['wikiUrl'], $productData['wikiUrl']);

        return $productData;
    }

    /**
     * Test POST /users 422
     */
    public function testPostProduct422UnprocessableEntity(): void
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
     * Test POST /products 400
     *
     * @param array $product product returned by testPostProduct201Ok()
     *
     * @depends testPostProduct201Ok
     */
    public function testPostProduct400BadRequest(array $product): void
    {
        // Mismo username
        $p_data = [
            'name' => $product['name'],
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
     * Test GET /products
     *
     * @depends testPostProduct201Ok
     */
    public function testCGetProducts200Ok(): void
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
        self::assertStringContainsString('products', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertArrayHasKey('products', $r_data);
        self::assertIsArray($r_data['products']);
    }

    /**
     * Test GET /products/productId
     *
     * @param array $product product returned by testPostProduct201Ok()
     *
     * @depends testPostProduct201Ok
     */
    public function testGetProduct200Ok(array $product): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $product['id'],
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
        $product_aux = json_decode((string) $response->getBody(), true);
        self::assertSame($product, $product_aux['product']);
    }

    /**
     * Test GET /products/productname/{productname} 204 Ok
     *
     * @param array $product product returned by testPostProduct201()
     *
     * @depends testPostProduct201Ok
     */
    public function testGetProductname204NoContent(array $product): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/productname/' . $product['name']
        );

        self::assertSame(
            204,
            $response->getStatusCode()
        );
        self::assertEmpty($response->getBody()->getContents());
    }

    /**
     * Test PUT /products/productId   209
     *
     * @param array $product product returned by testPostProduct201Ok()
     *
     * @depends testPostProduct201Ok
     *
     * @return array modified product data
     */
    public function testPutProduct209Updated(array $product): array
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
            self::RUTA_API . '/' . $product['id'],
            $p_data,
            $this->getTokenHeaders()
        );

        self::assertSame(209, $response->getStatusCode(), 'ERROR: ' . $response->getBody());
        self::assertJson((string) $response->getBody());
        $product_aux = json_decode((string) $response->getBody(), true);
        self::assertSame($product['id'], $product_aux['product']['id']);
        self::assertSame($p_data['name'], $product_aux['product']['name']);
        self::assertSame($p_data['birthDate'], $product_aux['product']['birthDate']);
        self::assertSame($p_data['deathDate'], $product_aux['product']['deathDate']);
        self::assertSame($p_data['imageUrl'], $product_aux['product']['imageUrl']);
        self::assertSame($p_data['wikiUrl'], $product_aux['product']['wikiUrl']);

        return $product_aux['product'];
    }

    /**
     * Test PUT /products 400
     *
     * @param array $product product returned by testPutProduct209Updated()
     *
     * @depends testPutProduct209Updated
     */
    public function testPutProduct400BadRequest(array $product): void
    {
        $p_data = [ 'name' => self::$faker->words(3, true) ];
        $this->runApp(
            'POST',
            self::RUTA_API,
            $p_data,
            $this->getTokenHeaders(parent::$writer['username'], parent::$writer['password'])
        );

        // productname already exists
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $product['id'],
            $p_data,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_BAD_REQUEST);
    }

    /**
     * Test OPTIONS /products[/productId]
     */
    public function testOptionsProduct200Ok(): void
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
     * Test DELETE /products/productId
     *
     * @param array $product product returned by testPostProduct201Ok()
     *
     * @depends testPostProduct201Ok
     * @depends testPostProduct400BadRequest
     * @depends testGetProduct200Ok
     * @depends testPostProduct422UnprocessableEntity
     * @depends testGetProductname204NoContent
     *
     * @return int productId
     */
    public function testDeleteProduct204NoContent(array $product): int
    {
        $response = $this->runApp(
            'DELETE',
            self::RUTA_API . '/' . $product['id'],
            null,
            $this->getTokenHeaders()
        );

        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty((string) $response->getBody());
        self::assertEmpty($response->getBody()->getContents());

        return $product['id'];
    }


    /**
     * Test GET /products/productname/{productname} 404 Not Found
     *
     * @param array $product product returned by testPutProduct209Updated()
     *
     * @depends testPutProduct209Updated
     * @depends testDeleteProduct204NoContent
     */
    public function testGetProductname404NotFound(array $product): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/productname/' . $product['name']
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test DELETE /products/productId 404 Not Found
     *
     * @param int $productId product id. returned by testDeleteProduct204NoContent()
     *
     * @depends testDeleteProduct204NoContent
     */
    public function testDeleteProduct404NotFound(int $productId): void
    {
        $response = $this->runApp(
            'DELETE',
            self::RUTA_API . '/' . $productId,
            null,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET /products/productId 404 Not Found
     *
     * @param int $productId product id. returned by testDeleteProduct204NoContent()
     *
     * @depends testDeleteProduct204NoContent
     */
    public function testGetProduct404NotFound(int $productId): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . $productId,
            null,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test PUT /products/productId 404 Not Found
     *
     * @param int $productId product id. returned by testDeleteProduct204NoContent()
     *
     * @depends testDeleteProduct204NoContent
     */
    public function testPutProduct404NotFound(int $productId): void
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . $productId,
            null,
            $this->getTokenHeaders()
        );
        $this->internalTestError($response, StatusCode::STATUS_NOT_FOUND);
    }

    /**
     * Test GET    /products 401 UNAUTHORIZED
     * Test POST   /products 401 UNAUTHORIZED
     * Test GET    /products/{productId} 401 UNAUTHORIZED
     * Test PUT    /products/{productId} 401 UNAUTHORIZED
     * Test DELETE /products/{productId} 401 UNAUTHORIZED
     *
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider401()
     * @return void
     */
    public function testProductStatus401Unauthorized(string $method, string $uri): void
    {
        $response = $this->runApp(
            $method,
            $uri
        );
        $this->internalTestError($response, StatusCode::STATUS_UNAUTHORIZED);
    }

    /**
     * Test POST   /products 403 FORBIDDEN
     * Test PUT    /products/{productId} 403 FORBIDDEN
     * Test DELETE /products/{productId} 403 FORBIDDEN
     *
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider403()
     * @return void
     */
    public function testProductStatus403Forbidden(string $method, string $uri): void
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
