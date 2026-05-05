<?php

namespace App\Controllers;

class Products extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        try {
            // Load products dynamically from database
            $query = $this->db->query("SELECT `Produkt-Nr` as id, Name as name, Preis as price FROM produkte ORDER BY Name");
            $productsFromDB = $query->getResult();

            $products = [];
            foreach ($productsFromDB as $product) {
                $products[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'currency' => '€'
                ];
            }

            $data['products'] = $products;

        } catch (\Exception $e) {
            // Fallback: If database fails, show some default products
            $data['products'] = [
                [
                    'id' => 1,
                    'name' => 'Nicht so E-Roller',
                    'price' => '29.99',
                    'currency' => '€'
                ],
                [
                    'id' => 2,
                    'name' => 'Disco Roller',
                    'price' => '999.98',
                    'currency' => '€'
                ]
            ];
            
            // You can log the error for debugging
            log_message('error', 'Database error in Products::index(): ' . $e->getMessage());
        }

        return view('products/index', $data);
    }

    public function addToCart()
    {
        // request to add product to cart
        $productId = $this->request->getPost('product_id');
        $quantity = $this->request->getPost('quantity');

        // response
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Produkt wurde zum Warenkorb hinzugefügt!'
        ]);
    }
}