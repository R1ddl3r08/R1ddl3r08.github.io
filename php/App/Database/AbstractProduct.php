<?php

namespace App\Database;
require_once __DIR__ . '/../../autoload.php';

abstract class AbstractProduct {
    public $pdo;
    public $sku;
    public $name;
    public $price;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function setSku($sku) {
        $this->sku = $sku;
    }

    public function getSku() {
        return $this->sku;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setPrice($price) {
        $this->price = $price;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getProduct()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE sku = :sku");

        $stmt->bindParam(':sku', $this->sku, \PDO::PARAM_STR);

        $stmt->execute();

        $product = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $product;
    }

    public function delete($productId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :product_id");

        $stmt->bindParam(':product_id', $productId, \PDO::PARAM_INT);

        $success = $stmt->execute();

        return ['success' => $success];
    }

}


?>