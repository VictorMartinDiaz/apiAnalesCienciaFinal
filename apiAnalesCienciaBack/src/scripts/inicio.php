<?php

/**
 * PHP version 7.4
 * src\scripts\inicio.php
 */

$projectRootDir = dirname(__DIR__, 2);
require_once $projectRootDir . '/vendor/autoload.php';

// Carga las variables de entorno
TDW\ACiencia\Utility\Utils::loadEnv($projectRootDir);
