<?php

namespace App\Database;
require_once __DIR__ . '/../../autoload.php';

class DVD extends AbstractProduct {
    public $size;

    public function setSize($size) {
        $this->size = $size;
    }

    public function getSize() {
        return $this->size;
    }

    public function getAllDVDs()
    {
        $sql = "SELECT d.*, p.name AS product_name, p.sku, p.price 
        FROM dvds d 
        JOIN products p ON d.product_id = p.id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $dvds = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($dvds as &$dvd) {
            $dvdObj = new DVD();
            $dvdObj->setName($dvd['product_name']);
            $dvdObj->setSku($dvd['sku']);
            $dvdObj->setPrice($dvd['price']);
            $dvdObj->setSize($dvd['size']);
            
            $dvd = [
                'product_id' => $dvd['product_id'],
                'name' => $dvdObj->getName(),
                'sku' => $dvdObj->getSku(),
                'price' => $dvdObj->getPrice(),
                'size' => $dvdObj->getSize()
            ];
        }
    
        return $dvds;
    }

    public function validateDVD()
    {
        $errors = [];
        
        if(empty($this->sku)){
            $errors['sku'] = 'The sku field is required';
        } else if (!empty($this->getProduct())) {
            $errors['sku'] = "The sku must be unique";
        }

        if(empty($this->name)){
            $errors['name'] = 'The name field is required';
        }

        if(empty($this->price)){
            $errors['price'] = 'The price field is required';
        } else if (!is_numeric($this->price)) {
            $errors['price'] = "The price fiels must only contain numbers";
        }

        if(empty($this->size)){
            $errors['size'] = 'The size field is required';
        } else if (!is_numeric($this->size)) {
            $errors['size'] = "The size fiels must only contain numbers";
        }

        return $errors;

    }

    public function save() {
        $errors = $this->validateDVD();
        if(!empty($errors)){
            $response['success'] = false;
            $response['errors'] = $errors;
            header("Content-Type: application/json");
            echo json_encode($response);
            return;
        }

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare("INSERT INTO products (name, sku, price) VALUES (:name, :sku, :price)");
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':sku', $this->sku);
            $stmt->bindParam(':price', $this->price);
            $stmt->execute();

            $productId = $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("INSERT INTO dvds (product_id, size) VALUES (:productId, :size)");
            $stmt->bindParam(':productId', $productId);
            $stmt->bindParam(':size', $this->size);
            $stmt->execute();

            $this->pdo->commit();

            $response['success'] = true;
            header("Content-Type: application/json");
            echo json_encode($response);
        } catch (\Exception $e) {
            $this->pdo->rollback();
            header("Content-Type: application/json");
            echo json_encode($e);
        }
    }
}

?>