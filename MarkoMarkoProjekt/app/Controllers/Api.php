<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Api extends ResourceController
{
    protected $format = 'json';
    
    public function createOrder()
    {
        // CORS Headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');
        
        // Handle preflight OPTIONS request
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }
        
        try {
            // Get JSON input
            $input = $this->request->getJSON(true);
            log_message('info', 'B2C API Request: ' . json_encode($input));
            
            // Validate API Key
            $expectedApiKey = 'b2c_to_b2b_secret_key_2024';
            if (isset($input['api_key']) && $input['api_key'] !== $expectedApiKey) {
                return $this->fail('Invalid API key', 401);
            }
            
            // Validate items
            if (!isset($input['items']) || !is_array($input['items']) || empty($input['items'])) {
                return $this->fail('No items provided', 400);
            }
            
            // Connect to database
            $db = \Config\Database::connect();
            
            // 🔥 STOCK CHECK - Before processing anything!
            log_message('info', 'Starting stock check for B2C order');
            
            foreach ($input['items'] as $item) {
                if (!isset($item['id'], $item['qty'], $item['price'])) {
                    return $this->fail('Invalid item data', 400);
                }
                
                $produktId = (int)$item['id'];
                $requestedQty = (int)$item['qty'];
                
                // Get product name for better error messages
                $productQuery = $db->query("SELECT Name FROM produkte WHERE `Produkt-Nr` = ?", [$produktId]);
                $productResult = $productQuery->getRow();
                
                if (!$productResult) {
                    return $this->fail("Product with ID {$produktId} not found", 404);
                }
                
                $productName = $productResult->Name;
                
                // Calculate current stock (EXACTLY like your B2B system)
                // Get total purchases
                $purchaseQuery = $db->query(
                    "SELECT COALESCE(SUM(Menge), 0) as total_purchases 
                     FROM `ek-transaktionen` 
                     WHERE `Produkt-Nr` = ?", 
                    [$produktId]
                );
                $purchaseResult = $purchaseQuery->getRow();
                $totalPurchases = $purchaseResult->total_purchases;
                
                // Get total sales
                $salesQuery = $db->query(
                    "SELECT COALESCE(SUM(Menge), 0) as total_sales 
                     FROM `vk-transaktionen` 
                     WHERE `Produkt-Nr` = ?", 
                    [$produktId]
                );
                $salesResult = $salesQuery->getRow();
                $totalSales = $salesResult->total_sales;
                
                // Calculate current stock
                $currentStock = $totalPurchases - $totalSales;
                
                log_message('info', "Stock check for {$productName} (ID: {$produktId}): Current stock: {$currentStock}, Requested: {$requestedQty}");
                
                // Check if enough stock available
                if ($requestedQty > $currentStock) {
                    $errorMessage = "Nicht genügend Bestand für '{$productName}'. Verfügbar: {$currentStock}, Angefragt: {$requestedQty}";
                    log_message('warning', "Stock check failed: {$errorMessage}");
                    
                    return $this->fail($errorMessage, 400);
                }
            }
            
            log_message('info', 'Stock check passed - proceeding with order');
            
            // Start transaction AFTER stock check passes
            $db->transStart();
            
            $gesamtmenge = 0;
            $gesamtpreis = 0;
            $processedItems = [];
            
            // Calculate totals (we already validated products above)
            foreach ($input['items'] as $item) {
                $productId = (int)$item['id'];
                $quantity = (int)$item['qty'];
                $priceFromB2C = (float)$item['price'];
                
                // Get product name (we know it exists from stock check)
                $productQuery = $db->query("SELECT Name FROM produkte WHERE `Produkt-Nr` = ?", [$productId]);
                $productResult = $productQuery->getRow();
                $productName = $productResult->Name;
                
                $itemTotal = $priceFromB2C * $quantity;
                
                $gesamtmenge += $quantity;
                $gesamtpreis += $itemTotal;
                
                $processedItems[] = [
                    'id' => $productId,
                    'name' => $productName,
                    'qty' => $quantity,
                    'price' => $priceFromB2C,
                    'total' => $itemTotal
                ];
                
                log_message('info', "Processed item: {$productName} x{$quantity} = {$itemTotal}€");
            }
            
            // Get new Verkauf-Nr
            $verkaufQuery = $db->query("SELECT MAX(`Verkauf-Nr`) as max_id FROM `verkaeufe`");
            $verkaufResult = $verkaufQuery->getRow();
            $verkaufNr = ($verkaufResult && $verkaufResult->max_id) ? $verkaufResult->max_id + 1 : 1;
            
            log_message('info', "New Verkauf-Nr: {$verkaufNr}");
            
            // Insert into verkaeufe
            $insertVerkauf = $db->query(
                "INSERT INTO `verkaeufe` (`Verkauf-Nr`, `Gesamtmenge`, `Preis`, `Datum`) VALUES (?, ?, ?, NOW())",
                [$verkaufNr, $gesamtmenge, $gesamtpreis]
            );
            
            if (!$insertVerkauf) {
                $dbError = $db->error();
                log_message('error', 'Verkaeufe insert error: ' . json_encode($dbError));
                $db->transRollback();
                return $this->fail("Fehler beim Speichern des Verkaufs: " . $dbError['message'], 500);
            }
            
            log_message('info', "Sale record created: Verkauf-Nr {$verkaufNr} with total {$gesamtpreis}€");
            
            // Get highest vk-transaktionen id
            $transQuery = $db->query("SELECT MAX(`id`) as max_id FROM `vk-transaktionen`");
            $transResult = $transQuery->getRow();
            $startId = ($transResult && $transResult->max_id) ? $transResult->max_id + 1 : 1;
            
            log_message('info', "Starting vk-transaktionen ID: {$startId}");
            
            // Insert each product into vk-transaktionen
            foreach ($processedItems as $index => $item) {
                $transaktionId = $startId + $index;
                $produktId = $item['id'];
                $menge = $item['qty'];
                $produktGesamtpreis = $item['total'];
                
                $insertTransaction = $db->query(
                    "INSERT INTO `vk-transaktionen` (`id`, `Transaktion-Nr`, `Produkt-Nr`, `Menge`, `Gesamtpreis`) VALUES (?, ?, ?, ?, ?)",
                    [$transaktionId, $verkaufNr, $produktId, $menge, $produktGesamtpreis]
                );
                
                if (!$insertTransaction) {
                    $dbError = $db->error();
                    log_message('error', "vk-transaktionen insert error for item {$index}: " . json_encode($dbError));
                    $db->transRollback();
                    return $this->fail("Fehler beim Speichern der Transaktion für Produkt {$produktId}: " . $dbError['message'], 500);
                }
                
                log_message('info', "Transaction created: ID {$transaktionId}, VerkaufNr {$verkaufNr}, ProductID {$produktId}");
            }
            
            // Complete transaction
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                $dbError = $db->error();
                log_message('error', 'Transaction failed: ' . json_encode($dbError));
                return $this->fail("Datenbankfehler beim Speichern der Bestellung: " . $dbError['message'], 500);
            }
            
            $response = [
                'success' => true,
                'message' => 'Bestellung erfolgreich aufgegeben!',
                'verkauf_nr' => $verkaufNr,
                'total_price' => round($gesamtpreis, 2),
                'total_quantity' => $gesamtmenge,
                'items_processed' => count($processedItems),
                'items' => $processedItems,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            log_message('info', 'B2C Order completed successfully: ' . json_encode($response));
            
            return $this->respond($response);
            
        } catch (\Exception $e) {
            log_message('error', 'B2C API Error: ' . $e->getMessage() . ' at line ' . $e->getLine() . ' in ' . $e->getFile());
            return $this->fail('Internal server error: ' . $e->getMessage(), 500);
        }
    }
    
    // Optional: Add a separate stock check endpoint for real-time validation
    public function checkStock()
    {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        
        try {
            $input = $this->request->getJSON(true);
            
            if (!isset($input['product_id'])) {
                return $this->fail('Product ID required', 400);
            }
            
            $produktId = (int)$input['product_id'];
            
            $db = \Config\Database::connect();
            
            // Get product info
            $productQuery = $db->query("SELECT Name FROM produkte WHERE `Produkt-Nr` = ?", [$produktId]);
            $productResult = $productQuery->getRow();
            
            if (!$productResult) {
                return $this->fail("Product not found", 404);
            }
            
            // Calculate stock
            $purchaseQuery = $db->query(
                "SELECT COALESCE(SUM(Menge), 0) as total_purchases FROM `ek-transaktionen` WHERE `Produkt-Nr` = ?", 
                [$produktId]
            );
            $totalPurchases = $purchaseQuery->getRow()->total_purchases;
            
            $salesQuery = $db->query(
                "SELECT COALESCE(SUM(Menge), 0) as total_sales FROM `vk-transaktionen` WHERE `Produkt-Nr` = ?", 
                [$produktId]
            );
            $totalSales = $salesQuery->getRow()->total_sales;
            
            $currentStock = $totalPurchases - $totalSales;
            
            return $this->respond([
                'success' => true,
                'product_id' => $produktId,
                'product_name' => $productResult->Name,
                'current_stock' => $currentStock,
                'available' => $currentStock > 0
            ]);
            
        } catch (\Exception $e) {
            return $this->fail('Error checking stock: ' . $e->getMessage(), 500);
        }
    }
    
    public function test()
    {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        
        try {
            $db = \Config\Database::connect();
            $tables = $db->listTables();
            
            return $this->respond([
                'success' => true,
                'message' => 'Test endpoint working',
                'tables' => $tables,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}