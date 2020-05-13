<?php

/**
 * PHP version 7.4
 * src/scripts/listUsers.php
 */

require __DIR__ . '/inicio.php';

use TDW\ACiencia\Entity\User;
use TDW\ACiencia\Utility\Utils;

try {
    $entityManager = Utils::getEntityManager();
    $users = $entityManager->getRepository(User::class)->findAll();
    $entityManager->close();
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}

// Salida formato JSON
if (in_array('--json', $argv, false)) {
    echo json_encode(['users' => $users], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    exit();
}

foreach ($users as $user) {
    echo $user . PHP_EOL;
}

echo sprintf("\nTotal: %d users.\n\n", count($users));
