<?php

namespace App\Controllers;

class Cart extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data = [
            'error_message' => '',
            'success_message' => ''
        ];

        return view('cart/index', $data);
    }

    public function checkout()
    {
        $error_message = '';
        $success_message = '';

        echo "<script>console.log('DEBUG: Checkout method called');</script>";

        try {
            // Get cart data from POST
            $cartData = $this->request->getPost('cartData');
            
            echo "<script>console.log('DEBUG: Raw cartData: " . addslashes($cartData ?? 'NULL') . "');</script>";
            
            if (empty($cartData)) {
                throw new \Exception("Warenkorb ist leer");
            }

            $cartData = json_decode($cartData, true);
            
            echo "<script>console.log('DEBUG: Decoded cartData: " . json_encode($cartData) . "');</script>";
            
            if (empty($cartData)) {
                throw new \Exception("Warenkorb ist leer oder ungültige Daten");
            }

            // Start transaction
            $this->db->transStart();
            echo "<script>console.log('DEBUG: Database transaction started');</script>";

            $gesamtmenge = 0;
            $gesamtpreis = 0;

            // Check stock for each item
            foreach ($cartData as $index => $item) {
                $produktId = $item['id'];
                $requestedQty = $item['qty'];

                echo "<script>console.log('DEBUG: Processing item $index - ProductID: $produktId, Qty: $requestedQty');</script>";

                // Get product name
                $productQuery = $this->db->query("SELECT Name FROM produkte WHERE `Produkt-Nr` = ?", [$produktId]);
                $productResult = $productQuery->getRow();
                $productName = $productResult ? $productResult->Name : "Produkt #" . $produktId;

                echo "<script>console.log('DEBUG: Product name: " . addslashes($productName) . "');</script>";

                // Get total purchases
                $purchaseQuery = $this->db->query(
                    "SELECT COALESCE(SUM(Menge), 0) as total_purchases 
                     FROM `ek-transaktionen` 
                     WHERE `Produkt-Nr` = ?", 
                    [$produktId]
                );
                $purchaseResult = $purchaseQuery->getRow();
                $totalPurchases = $purchaseResult->total_purchases;

                echo "<script>console.log('DEBUG: Total purchases for product $produktId: $totalPurchases');</script>";

                // Get total sales
                $salesQuery = $this->db->query(
                    "SELECT COALESCE(SUM(Menge), 0) as total_sales 
                     FROM `vk-transaktionen` 
                     WHERE `Produkt-Nr` = ?", 
                    [$produktId]
                );
                $salesResult = $salesQuery->getRow();
                $totalSales = $salesResult->total_sales;

                echo "<script>console.log('DEBUG: Total sales for product $produktId: $totalSales');</script>";

                // Calculate current stock
                $currentStock = $totalPurchases - $totalSales;

                echo "<script>console.log('DEBUG: Current stock for product $produktId: $currentStock (purchases: $totalPurchases - sales: $totalSales)');</script>";

                // Check if enough stock
                if ($requestedQty > $currentStock) {
                    throw new \Exception("Nicht genügend Bestand für '" . $productName . "'. Verfügbar: " . $currentStock . ", Angefragt: " . $requestedQty);
                }

                $gesamtmenge += $item['qty'];
                $gesamtpreis += $item['qty'] * $item['price'];
            }

            echo "<script>console.log('DEBUG: Total calculated - Menge: $gesamtmenge, Preis: $gesamtpreis');</script>";

            // Everything ok? Create sale record

            // Get new Verkauf-Nr
            $verkaufQuery = $this->db->query("SELECT MAX(`Verkauf-Nr`) as max_id FROM `verkaeufe`");
            $verkaufResult = $verkaufQuery->getRow();
            $verkaufNr = ($verkaufResult && $verkaufResult->max_id) ? $verkaufResult->max_id + 1 : 1;

            echo "<script>console.log('DEBUG: New Verkauf-Nr: $verkaufNr');</script>";

            // Insert into verkaeufe
            $insertVerkauf = $this->db->query(
                "INSERT INTO `verkaeufe` (`Verkauf-Nr`, `Gesamtmenge`, `Preis`, `Datum`) VALUES (?, ?, ?, NOW())",
                [$verkaufNr, $gesamtmenge, $gesamtpreis]
            );

            echo "<script>console.log('DEBUG: Verkaeufe insert result: " . ($insertVerkauf ? 'SUCCESS' : 'FAILED') . "');</script>";

            if (!$insertVerkauf) {
                $dbError = $this->db->error();
                echo "<script>console.log('DEBUG: Verkaeufe insert error: " . json_encode($dbError) . "');</script>";
                throw new \Exception("Fehler beim Speichern des Verkaufs: " . $dbError['message']);
            }

            // Get highest vk-transaktionen id
            $transQuery = $this->db->query("SELECT MAX(`id`) as max_id FROM `vk-transaktionen`");
            $transResult = $transQuery->getRow();
            $startId = ($transResult && $transResult->max_id) ? $transResult->max_id + 1 : 1;

            echo "<script>console.log('DEBUG: Starting vk-transaktionen ID: $startId');</script>";

            // Insert each product into vk-transaktionen
            foreach ($cartData as $index => $item) {
                $transaktionId = $startId + $index;
                $produktId = $item['id'];
                $menge = $item['qty'];
                $produktGesamtpreis = $item['qty'] * $item['price'];

                echo "<script>console.log('DEBUG: Inserting vk-transaktion - ID: $transaktionId, VerkaufNr: $verkaufNr, ProductID: $produktId, Menge: $menge, Preis: $produktGesamtpreis');</script>";

                $insertTransaction = $this->db->query(
                    "INSERT INTO `vk-transaktionen` (`id`, `Transaktion-Nr`, `Produkt-Nr`, `Menge`, `Gesamtpreis`) VALUES (?, ?, ?, ?, ?)",
                    [$transaktionId, $verkaufNr, $produktId, $menge, $produktGesamtpreis]
                );

                echo "<script>console.log('DEBUG: vk-transaktionen insert result for item $index: " . ($insertTransaction ? 'SUCCESS' : 'FAILED') . "');</script>";

                if (!$insertTransaction) {
                    $dbError = $this->db->error();
                    echo "<script>console.log('DEBUG: vk-transaktionen insert error for item $index: " . json_encode($dbError) . "');</script>";
                    throw new \Exception("Fehler beim Speichern der Transaktion für Produkt $produktId: " . $dbError['message']);
                }
            }

            // Complete transaction
            $this->db->transComplete();
            echo "<script>console.log('DEBUG: Database transaction completed');</script>";

            if ($this->db->transStatus() === false) {
                $dbError = $this->db->error();
                echo "<script>console.log('DEBUG: Transaction failed: " . json_encode($dbError) . "');</script>";
                throw new \Exception("Datenbankfehler beim Speichern der Bestellung: " . $dbError['message']);
            }

            echo "<script>console.log('DEBUG: Transaction successful!');</script>";

            $success_message = 'Bestellung erfolgreich aufgegeben! Verkauf-Nr: ' . $verkaufNr;

        } catch (\Exception $e) {
            echo "<script>console.log('DEBUG: Exception caught: " . addslashes($e->getMessage()) . "');</script>";
            $error_message = $e->getMessage();
        }

        $data = [
            'error_message' => $error_message,
            'success_message' => $success_message
        ];

        return view('cart/index', $data);
    }
}