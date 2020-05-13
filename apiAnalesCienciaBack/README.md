![logo UPM](https://raw.githubusercontent.com/laracabrera/AOS/master/tarea1/logo_upm.jpg)  TDW: REST API - Anales de la Ciencia
======================================

[![MIT license](http://img.shields.io/badge/license-MIT-brightgreen.svg)](http://opensource.org/licenses/MIT)
[![Minimum PHP Version](https://img.shields.io/badge/php-%5E7.4-blue.svg)](http://php.net/)
> Implementación de una API REST para la gestión aportaciones a la Ciencia.

Esta aplicación implementa una interfaz de programación [REST][rest] desarrollada sobre
el framework [Slim][slim]. La aplicación proporcionará las operaciones
habituales para la gestión de Productos, Entidades y Personas.

Para hacer más sencilla la gestión de los datos se ha utilizado
el ORM [Doctrine][doctrine]. Doctrine 2 es un Object-Relational Mapper que proporciona
persistencia transparente para objetos PHP. Utiliza el patrón [Data Mapper][dataMapper]
con el objetivo de obtener un desacoplamiento completo entre la lógica de negocio y la
persistencia de los datos en los sistemas de gestión de bases de datos (SGBD).

Para su configuración, este proyecto se apoya en el componente [DotENV][dotenv], que
permite realizar la configuración a través de variables de entorno. De esta manera,
cualquier configuración que pueda variar entre diferentes entornos puede ser establecida
en variables de entorno, tal como se aconseja en la metodología [“The twelve-factor app”][12factor].

Por otra parte se incluye parcialmente la especificación de la API (OpenAPI 3.0). Esta
especificación se ha elaborado empleando el editor [Swagger][swagger]. Adicionalmente 
también se incluye la interfaz de usuario (SwaggerUI) de esta fenomenal herramienta que permite
realizar pruebas interactivas de manera completa y elegante. La especificación entregada
define las operaciones sobre usuarios del sistema y los Productos, por lo que queda por implementar las 
operaciones relativas a la gestión de Entidades y Personas.

## Instalación de la aplicación

El primer paso consiste en generar un esquema de base de datos vacío y un usuario/contraseña
con privilegios completos sobre dicho esquema.

A continuación se deberá crear una copia del fichero `./.env` y renombrarla
como `./.env.local`. Después se debe editar dicho fichero y modificar las variables `DATABASE_NAME`,
`DATABASE_USER` y `DATABASE_PASSWD` con los valores generados en el paso anterior (el resto de opciones
pueden quedar como comentarios). Una vez editado el anterior fichero y desde el directorio raíz del
proyecto se deben ejecutar los comandos:
```
$ composer install
$ bin/doctrine orm:schema:update --dump-sql --force
```
Para verificar la validez de la información de mapeo y la sincronización con la base de datos:
```
$ bin/doctrine orm:validate
```

## Estructura del proyecto:

A continuación se describe el contenido y estructura más destacado del proyecto:

* Directorio `bin`:
    - Ejecutables (*doctrine*, *phpunit*, ...)
* Directorio `config`:
    - `cli-config.php`: configuración de la consola de comandos de Doctrine
* Directorio `src`:
    - Subdirectorio `src/Entity`: entidades PHP (incluyen anotaciones de mapeo del ORM)
    - Subdirectorio `src/Controller`: controladores PHP (implementan los _endpoints_ de la API)
    - Subdirectorio `src/scripts`: scripts de ejemplo
* Directorio `vendor`:
    - Componentes desarrollados por terceros (Doctrine, DotENV, Slim, etc.)

## Ejecución de pruebas

La aplicación incorpora un conjunto completo de herramientas para la ejecución de pruebas 
unitarias y de integración con [PHPUnit][phpunit]. Empleando este conjunto de herramientas
es posible comprobar de manera automática el correcto funcionamiento de la API completa
sin la necesidad de herramientas adicionales.

Para configurar el entorno de pruebas se debe crear un nuevo esquema de bases de datos vacío,
y una copia del fichero `./phpunit.xml.dist` y renombrarla como `./phpunit.xml`.
Después se debe editar este último fichero para asignar los siguientes parámetros:
                                                                            
* Configuración (líneas 17-19) del acceso a la nueva base de datos (`DATABASE_NAME`, `DATABASE_USER`
y `DATABASE_PASSWD`)
* Si se desea (líneas 23-25), se pueden modificar el nombre y contraseña de los usuarios que se van
a emplear para realizar las pruebas (no es necesario insertarlos, lo hace automáticamente
el método `setUpBeforeClass()` de la clase `BaseTestCase`)

Para lanzar la suite de pruebas completa se debe ejecutar:
```
$ bin/phpunit [--testdox] [--coverage-text]
```

[dataMapper]: http://martinfowler.com/eaaCatalog/dataMapper.html
[doctrine]: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/
[dotenv]: https://packagist.org/packages/vlucas/phpdotenv
[jwt]: https://jwt.io/
[lh]: https://localhost:8000/
[monolog]: https://github.com/Seldaek/monolog
[openapi]: https://www.openapis.org/
[phpunit]: http://phpunit.de/manual/current/en/index.html
[rest]: http://www.restapitutorial.com/
[slim]: https://www.slimframework.com/ 
[swagger]: http://swagger.io/
[yaml]: https://yaml.org/
[12factor]: https://www.12factor.net/es/