<?php

namespace App\Database;
require_once __DIR__ . '/../../autoload.php';

class Book extends AbstractProduct {
    public $weight;

    public function setWeight($weight) {
        $this->weight = $weight;
    }

    public function getWeight() {
        return $this->weight;
    }

    public function save() {
        $errors = $this->validateBook();
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

            $stmt = $this->pdo->prepare("INSERT INTO books (product_id, weight) VALUES (:productId, :weight)");
            $stmt->bindParam(':productId', $productId);
            $stmt->bindParam(':weight', $this->weight);
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

    public function getAllBooks()
    {
        $sql = "SELECT b.*, p.name AS product_name, p.sku, p.price 
                FROM books b 
                JOIN products p ON b.product_id = p.id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $books = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($books as &$book) {
            $bookObj = new Book();
            $bookObj->setName($book['product_name']);
            $bookObj->setSku($book['sku']);
            $bookObj->setPrice($book['price']);
            $bookObj->setWeight($book['weight']);
            
            $book = [
                'product_id' => $book['product_id'],
                'name' => $bookObj->getName(),
                'sku' => $bookObj->getSku(),
                'price' => $bookObj->getPrice(),
                'weight' => $bookObj->getWeight()
            ];
        }
        
        return $books;
    }

    public function validateBook()
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

        if(empty($this->weight)){
            $errors['weight'] = 'The weight field is required';
        } else if (!is_numeric($this->weight)) {
            $errors['weight'] = "The weight fiels must only contain numbers";
        }

        return $errors;

    }
}

?>