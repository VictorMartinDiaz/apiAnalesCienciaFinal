<?php

/**
 * PHP version 7.4
 * src/scripts/removePerson.php
 */

use TDW\ACiencia\Entity\Person;
use TDW\ACiencia\Utility\Utils;

require __DIR__ . '/inicio.php';

if ($argc !== 2) {
    $texto = <<< ______USO

    *> Usage: ${argv[0]} <personId>
    Deletes the person specified by <personId>

______USO;
    die($texto);
}

try {
    $personId = (int) $argv[1];
    $entityManager = Utils::getEntityManager();
    $person = $entityManager
        ->find(Person::class, $personId);
    if (null === $person) {
        exit('Person [' . $personId . '] not exist.' . PHP_EOL);
    }
    $entityManager->remove($person);
    $entityManager->flush();
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}
