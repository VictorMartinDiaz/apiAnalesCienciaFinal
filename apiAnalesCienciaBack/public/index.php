<?php

/**
 * PHP version 7.4
 * public/index.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 *
 * @link     https://www.slimframework.com/docs/v4/concepts/life-cycle.html
 */

use TDW\ACiencia\Utility\Utils;

$baseDir = dirname(__DIR__);
require_once $baseDir . '/vendor/autoload.php';

// 1. Create DI Container + Instantiation
Utils::loadEnv($baseDir);
(require $baseDir . '/config/bootstrap.php')->run();
