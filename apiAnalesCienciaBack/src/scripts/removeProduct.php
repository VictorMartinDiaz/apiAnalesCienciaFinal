<?php

/**
 * PHP version 7.4
 * src/scripts/removeProduct.php
 */

use TDW\ACiencia\Entity\Product;
use TDW\ACiencia\Utility\Utils;

require __DIR__ . '/inicio.php';

if ($argc !== 2) {
    $texto = <<< ______USO

    *> Usage: ${argv[0]} <productId>
    Deletes the product specified by <productId>

______USO;
    die($texto);
}

try {
    $productId = (int) $argv[1];
    $entityManager = Utils::getEntityManager();
    $product = $entityManager
        ->find(Product::class, $productId);
    if (null === $product) {
        exit('Product [' . $productId . '] not exist.' . PHP_EOL);
    }
    $entityManager->remove($product);
    $entityManager->flush();
} catch (Throwable $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}
