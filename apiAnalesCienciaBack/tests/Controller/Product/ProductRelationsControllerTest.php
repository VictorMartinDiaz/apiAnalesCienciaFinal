<?php

/**
 * PHP version 7.4
 * tests/Controller/Product/ProductRelationsControllerTest.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\Test\ACiencia\Controller\Product;

use Doctrine\ORM\EntityManagerInterface;
use TDW\ACiencia\Entity\Entity;
use TDW\ACiencia\Entity\Person;
use TDW\ACiencia\Entity\Product;
use TDW\ACiencia\Utility\Utils;
use TDW\Test\ACiencia\Controller\BaseTestCase;

/**
 * Class ProductRelationsControllerTest
 */
final class ProductRelationsControllerTest extends BaseTestCase
{
    /** @var string Path para la gestión de usuarios */
    protected const RUTA_API = '/api/v1/products';

    /** @var array Admin data */
    protected static array $writer;

    /** @var array reader user data */
    protected static array $reader;

    protected static EntityManagerInterface $entityManager;

    private static Product $product;
    private static Entity $entity;
    private static Person $person;

    /**
     * Se ejecuta una vez al inicio de las pruebas de la clase UserControllerTest
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // load user admin fixtures
        parent::$writer['id'] = Utils::loadUserData(
            parent::$writer['username'],
            parent::$writer['email'],
            parent::$writer['password'],
            true
        );

        // load user reader fixtures
        self::$reader = [
            'username' => self::$faker->userName,
            'email'    => self::$faker->email,
            'password' => self::$faker->password,
        ];
        self::$reader['id'] = Utils::loadUserData(
            self::$reader['username'],
            self::$reader['email'],
            self::$reader['password'],
            false
        );

        // create and insert fixtures
        self::$product = new Product(self::$faker->word);
        self::$entity  = new Entity(self::$faker->company);
        self::$person  = new Person(self::$faker->name);

        self::$entityManager = Utils::getEntityManager();
        self::$entityManager->persist(self::$product);
        self::$entityManager->persist(self::$entity);
        self::$entityManager->persist(self::$person);
        self::$entityManager->flush();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    // *******************
    // Product -> Entities
    // *******************
    /**
     * PUT /products/{productId}/entities/add/{idEntity}
     */
    public function testAddEntity()
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . self::$product->getId()
                . '/entities/add/' . self::$entity->getId(),
            null,
            $this->getTokenHeaders(parent::$writer['username'], parent::$writer['password'])
        );

        self::assertSame(209, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
    }

    /**
     * GET /products/{productId}/entities 200 Ok
     *
     * @depends testAddEntity
     */
    public function testGetEntities200OkWithElements(): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . self::$product->getId() . '/entities',
            null,
            $this->getTokenHeaders(self::$reader['username'], self::$reader['password'])
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $responseEntities = json_decode((string) $response->getBody(), true);
        self::assertArrayHasKey('entities', $responseEntities);
        self::assertSame(
            self::$entity->getName(),
            $responseEntities['entities'][0]['entity']['name']
        );
    }

    /**
     * PUT /products/{productId}/entities/rem/{idEntity}
     *
     * @depends testGetEntities200OkWithElements
     */
    public function testRemoveEntity()
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . self::$product->getId()
            . '/entities/rem/' . self::$entity->getId(),
            null,
            $this->getTokenHeaders(parent::$writer['username'], parent::$writer['password'])
        );

        self::assertSame(209, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $responseProduct = json_decode((string) $response->getBody(), true);
        self::assertArrayHasKey('entities', $responseProduct['product']);
        self::assertEmpty($responseProduct['product']['entities']);
    }

    /**
     * GET /products/{productId}/entities 200 Ok - Empty
     *
     * @depends testRemoveEntity
     */
    public function testGetEntities200OkEmpty(): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . self::$product->getId() . '/entities',
            null,
            $this->getTokenHeaders(self::$reader['username'], self::$reader['password'])
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $responseEntities = json_decode((string) $response->getBody(), true);
        self::assertArrayHasKey('entities', $responseEntities);
        self::assertEmpty($responseEntities['entities']);
    }

    // ******************
    // Product -> Persons
    // ******************
    /**
     * PUT /products/{productId}/persons/add/{idPerson}
     */
    public function testAddPerson()
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . self::$product->getId()
            . '/persons/add/' . self::$person->getId(),
            null,
            $this->getTokenHeaders(parent::$writer['username'], parent::$writer['password'])
        );

        self::assertSame(209, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
    }

    /**
     * GET /products/{productId}/persons 200 Ok
     *
     * @depends testAddPerson
     */
    public function testGetPersons200OkWithElements(): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . self::$product->getId() . '/persons',
            null,
            $this->getTokenHeaders(self::$reader['username'], self::$reader['password'])
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $responsePersons = json_decode((string) $response->getBody(), true);
        self::assertArrayHasKey('persons', $responsePersons);
        self::assertSame(
            self::$person->getName(),
            $responsePersons['persons'][0]['person']['name']
        );
    }

    /**
     * PUT /products/{productId}/persons/rem/{idPerson}
     *
     * @depends testGetPersons200OkWithElements
     */
    public function testRemovePerson()
    {
        $response = $this->runApp(
            'PUT',
            self::RUTA_API . '/' . self::$product->getId()
            . '/persons/rem/' . self::$person->getId(),
            null,
            $this->getTokenHeaders(parent::$writer['username'], parent::$writer['password'])
        );

        self::assertSame(209, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $responseProduct = json_decode((string) $response->getBody(), true);
        self::assertArrayHasKey('persons', $responseProduct['product']);
        self::assertEmpty($responseProduct['product']['persons']);
    }

    /**
     * GET /products/{productId}/persons 200 Ok - Empty
     *
     * @depends testRemovePerson
     */
    public function testGetPersons200OkEmpty(): void
    {
        $response = $this->runApp(
            'GET',
            self::RUTA_API . '/' . self::$product->getId() . '/persons',
            null,
            $this->getTokenHeaders(self::$reader['username'], self::$reader['password'])
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $responsePersons = json_decode((string) $response->getBody(), true);
        self::assertArrayHasKey('persons', $responsePersons);
        self::assertEmpty($responsePersons['persons']);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param int $status
     * @param string $user
     * @return void
     *
     * @dataProvider routeExceptionProvider()
     */
    public function testProductRelationshipErrors(string $method, string $uri, int $status, string $user = ''): void
    {
        if ('admin' === $user) {
            $requestingUser = parent::$writer;
        } elseif ('reader' === $user) {
            $requestingUser = self::$reader;
        } else {
            $requestingUser = ['username' => '', 'password' => ''];
        }

        $response = $this->runApp(
            $method,
            $uri,
            null,
            $this->getTokenHeaders($requestingUser['username'], $requestingUser['password'])
        );
        $this->internalTestError($response, $status);
    }

    // --------------
    // DATA PROVIDERS
    // --------------

    /**
     * Route provider (expected status: 404 NOT FOUND)
     *
     * @return array [ method, url, status, user ]
     */
    public function routeExceptionProvider(): array
    {
        return [
            // 401
            'getEntities401'     => [ 'GET', self::RUTA_API . '/1/entities',       401],
            'putAddEntity401'    => [ 'PUT', self::RUTA_API . '/1/entities/add/1', 401],
            'putRemoveEntity401' => [ 'PUT', self::RUTA_API . '/1/entities/rem/1', 401],
            'getPersons401'      => [ 'GET', self::RUTA_API . '/1/persons',        401],
            'putAddPerson401'    => [ 'PUT', self::RUTA_API . '/1/persons/add/1',  401],
            'putRemovePerson401' => [ 'PUT', self::RUTA_API . '/1/persons/rem/1',  401],

            // 403
            'putAddEntity403'    => [ 'PUT', self::RUTA_API . '/1/entities/add/1', 403, 'reader'],
            'putRemoveEntity403' => [ 'PUT', self::RUTA_API . '/1/entities/rem/1', 403, 'reader'],
            'putAddPerson403'    => [ 'PUT', self::RUTA_API . '/1/persons/add/1',  403, 'reader'],
            'putRemovePerson403' => [ 'PUT', self::RUTA_API . '/1/persons/rem/1',  403, 'reader'],

            // 404
            'getEntities404'     => [ 'GET', self::RUTA_API . '/0/entities',       404, 'admin'],
            'putAddEntity404'    => [ 'PUT', self::RUTA_API . '/0/entities/add/1', 404, 'admin'],
            'putRemoveEntity404' => [ 'PUT', self::RUTA_API . '/0/entities/rem/1', 404, 'admin'],
            'getPersons404'      => [ 'GET', self::RUTA_API . '/0/persons',        404, 'admin'],
            'putAddPerson404'    => [ 'PUT', self::RUTA_API . '/0/persons/add/1',  404, 'admin'],
            'putRemovePerson404' => [ 'PUT', self::RUTA_API . '/0/persons/rem/1',  404, 'admin'],

            // 406
            'putAddEntity406'    => [ 'PUT', self::RUTA_API . '/1/entities/add/100', 406, 'admin'],
            'putRemoveEntity406' => [ 'PUT', self::RUTA_API . '/1/entities/rem/100', 406, 'admin'],
            'putAddPerson406'    => [ 'PUT', self::RUTA_API . '/1/persons/add/100',  406, 'admin'],
            'putRemovePerson406' => [ 'PUT', self::RUTA_API . '/1/persons/rem/100',  406, 'admin'],
        ];
    }
}
