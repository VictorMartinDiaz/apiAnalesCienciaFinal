<?php

/**
 * PHP version 7.4
 * src/Utility/Utils.php
 */

namespace TDW\ACiencia\Utility;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use TDW\ACiencia\Entity\Role;
use TDW\ACiencia\Entity\User;

/**
 * Trait Utils
 */
trait Utils
{
    /**
     * Generate the Entity Manager
     *
     * @return EntityManagerInterface
     */
    public static function getEntityManager(): EntityManagerInterface
    {
        if (
            !isset(
                $_ENV['DATABASE_NAME'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWD'],
                $_ENV['ENTITY_DIR']
            )
        ) {
            fwrite(STDERR, 'Faltan variables de entorno por definir' . PHP_EOL);
            exit(1);
        }

        // Cargar configuración de la conexión
        $dbParams = [
            'host'      => $_ENV['DATABASE_HOST'] ?? '127.0.0.1',
            'port'      => $_ENV['DATABASE_PORT'] ?? 3306,
            'dbname'    => $_ENV['DATABASE_NAME'],
            'user'      => $_ENV['DATABASE_USER'],
            'password'  => $_ENV['DATABASE_PASSWD'],
            'driver'    => $_ENV['DATABASE_DRIVER'] ?? 'pdo_mysql',
            'charset'   => $_ENV['DATABASE_CHARSET'] ?? 'UTF8',
        ];

        $entityDir = dirname(__DIR__, 2) . $_ENV['ENTITY_DIR'];
        $debug = $_ENV['DEBUG'] ?? false;
        $config = Setup::createAnnotationMetadataConfiguration(
            [ $entityDir ],            // paths to mapped entities
            $debug,   // developper mode
            ini_get('sys_temp_dir'),   // Proxy dir
            null,                      // Cache implementation
            false                      // use Simple Annotation Reader
        );
        $config->setAutoGenerateProxyClasses(true);
        if ($debug) {
            $config->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
        }

        try {
            $entityManager = EntityManager::create($dbParams, $config);
        } catch (\Throwable $e) {
            $msg = sprintf('ERROR (%d): %s', $e->getCode(), $e->getMessage());
            fwrite(STDERR, $msg . PHP_EOL);
            exit(1);
        }

        return $entityManager;
    }

    /**
     * Load the environment/configuration variables
     * defined in .env file + (.env.docker || .env.local)
     *
     * @param string $dir   project root directory
     */
    public static function loadEnv(string $dir): void
    {
        require_once $dir . '/vendor/autoload.php';

        if (!class_exists(\Dotenv\Dotenv::class)) {
            fwrite(STDERR, 'ERROR: No se ha cargado la clase DotENV' . PHP_EOL);
            exit(1);
        }

        // Load environment variables from .env file
        if (file_exists($dir . '/.env')) {
            $dotenv = \Dotenv\Dotenv::createMutable($dir, '.env');
            $dotenv->load();
        } else {
            fwrite(STDERR, 'ERROR: no existe el fichero .env' . PHP_EOL);
            exit(1);
        }

        // Overload (if they exist) with .env.docker or .env.local
        if (isset($_SERVER['DOCKER'])) {
            $dotenv = \Dotenv\Dotenv::createMutable($dir, '.env.docker');
            $dotenv->load();
        } elseif (file_exists($dir . '/.env.local')) {
            $dotenv = \Dotenv\Dotenv::createMutable($dir, '.env.local');
            $dotenv->load();
        }
    }

    /**
     * Drop & Update database schema
     *
     * @return void
     */
    public static function updateSchema(): void
    {
        try {
            $e_manager = self::getEntityManager();
            $metadata = $e_manager->getMetadataFactory()->getAllMetadata();
            $sch_tool = new SchemaTool($e_manager);
            $sch_tool->dropDatabase();
            $sch_tool->updateSchema($metadata, false);
        } catch (\Throwable $e) {
            fwrite(
                STDERR,
                'EXCEPCIÓN: ' . $e->getCode() . ' - ' . $e->getMessage()
            );
            exit(1);
        }
    }

    /**
     * Load user data fixtures
     *
     * @param string $username user name
     * @param string $email user email
     * @param string $password user password
     * @param bool $isWriter isAdmin
     *
     * @return int user_id
     */
    public static function loadUserData(
        string $username,
        string $email,
        string $password,
        bool $isWriter = false
    ): int {
        $user = new User(
            $username,
            $email,
            $password,
            ($isWriter) ? Role::ROLE_WRITER : Role::ROLE_READER
        );
        try {
            $e_manager = self::getEntityManager();
            $e_manager->persist($user);
            $e_manager->flush();
        } catch (\Exception $e) {
            fwrite(STDERR, 'EXCEPCIÓN: ' . $e->getCode() . ' - ' . $e->getMessage());
            exit(1);
        }

        return $user->getId();
    }
}
