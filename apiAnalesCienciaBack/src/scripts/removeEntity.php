<?php

/**
 * PHP version 7.4
 * src/scripts/removeEntity.php
 */

use TDW\ACiencia\Entity\Entity;
use TDW\ACiencia\Utility\Utils;

require __DIR__ . '/inicio.php';

if ($argc !== 2) {
    $texto = <<< ______USO

    *> Usage: ${argv[0]} <entityId>
    Deletes the entity specified by <entityId>

______USO;
    die($texto);
}

try {
    $entityId = (int) $argv[1];
    $entityManager = Utils::getEntityManager();
    /** @var Entity $entity */
    $entity = $entityManager
        ->find(Entity::class, $entityId);
    if (null === $entity) {
        exit('Entity [' . $entityId . '] not exist.' . PHP_EOL);
    }
    $entityManager->remove($entity);
    $entityManager->flush();
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}
