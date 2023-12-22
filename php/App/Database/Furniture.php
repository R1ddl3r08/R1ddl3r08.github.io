<?php

namespace App\Database;
require_once __DIR__ . '/../../autoload.php';

class Furniture extends AbstractProduct {
    public $height;
    public $width;
    public $length;

    public function setHeight($height) {
        $this->height = $height;
    }

    public function getHeight() {
        return $this->height;
    }

    public function setWidth($width) {
        $this->width = $width;
    }

    public function getWidth() {
        return $this->width;
    }

    public function setLength($length) {
        $this->length = $length;
    }

    public function getLength() {
        return $this->length;
    }

    public function getAllFurnitures()
    {
        $sql = "SELECT f.*, p.name AS product_name, p.sku, p.price 
        FROM furnitures f 
        JOIN products p ON f.product_id = p.id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $furnitures = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($furnitures as &$furniture) {
            $furnitureObj = new Furniture();
            $furnitureObj->setName($furniture['product_name']);
            $furnitureObj->setSku($furniture['sku']);
            $furnitureObj->setPrice($furniture['price']);
            $furnitureObj->setHeight($furniture['height']);
            $furnitureObj->setWidth($furniture['width']);
            $furnitureObj->setLength($furniture['length']);
            
            $furniture = [
                'product_id' => $furniture['product_id'],
                'name' => $furnitureObj->getName(),
                'sku' => $furnitureObj->getSku(),
                'price' => $furnitureObj->getPrice(),
                'height' => $furnitureObj->getHeight(),
                'width' => $furnitureObj->getWidth(),
                'length' => $furnitureObj->getLength(),
            ];
        }
    
        return $furnitures;
    }

    public function validateFurniture()
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

        if(empty($this->height)){
            $errors['height'] = 'The height field is required';
        } else if (!is_numeric($this->height)) {
            $errors['height'] = "The height fiels must only contain numbers";
        }

        if(empty($this->height)){
            $errors['width'] = 'The width field is required';
        } else if (!is_numeric($this->width)) {
            $errors['width'] = "The width fiels must only contain numbers";
        }

        if(empty($this->height)){
            $errors['length'] = 'The length field is required';
        } else if (!is_numeric($this->length)) {
            $errors['length'] = "The length fiels must only contain numbers";
        }

        return $errors;

    }

    public function save() {
        $errors = $this->validateFurniture();
        if(!empty($errors)){
            $response['success'] = false;
            $response['errors'] = $errors;
            header("Content-Type: application/json");
            echo json_encode($response);
        }

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare("INSERT INTO products (name, sku, price) VALUES (:name, :sku, :price)");
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':sku', $this->sku);
            $stmt->bindParam(':price', $this->price);
            $stmt->execute();

            $productId = $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("INSERT INTO furnitures (product_id, height, width, length) VALUES (:productId, :height, :width, :length)");
            $stmt->bindParam(':productId', $productId);
            $stmt->bindParam(':height', $this->height);
            $stmt->bindParam(':width', $this->width);
            $stmt->bindParam(':length', $this->length);
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