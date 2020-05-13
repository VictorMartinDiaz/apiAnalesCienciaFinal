<?php

/**
 * PHP version 7.4
 * tests/tests_bootstrap.php
 */

error_reporting(E_ALL & E_STRICT);
ini_set('display_errors', '1');

mt_srand();

// Create/update tables in the test database
TDW\ACiencia\Utility\Utils::updateSchema();
