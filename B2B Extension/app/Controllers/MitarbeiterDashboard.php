<?php

namespace App\Controllers;

class MitarbeiterDashboard extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // Check if user is logged in
        if (!session()->get('mitarbeiter_logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $error_message = '';
        $success_message = '';

        // Handle purchase stock if form was submitted
        if (strtolower($this->request->getMethod()) === 'post' && $this->request->getPost('action') === 'purchase_stock') {
            
            // DEBUG: Add debug info to the response
            echo "<script>console.log('DEBUG: POST request received');</script>";
            echo "<script>console.log('DEBUG: POST data: " . json_encode($_POST) . "');</script>";
            
            try {
                // Get form data
                $productId = $this->request->getPost('product_id');
                $quantity = (int)$this->request->getPost('quantity');
                $employeeId = session()->get('mitarbeiter_id');

                // DEBUG: Log the extracted values
                echo "<script>console.log('DEBUG: ProductID: $productId, Quantity: $quantity, EmployeeID: $employeeId');</script>";

                // Validate input
                if (!$productId || $quantity <= 0) {
                    throw new \Exception('Bitte wählen Sie ein Produkt und geben Sie eine gültige Menge ein.');
                }

                // Start transaction
                $this->db->transStart();
                echo "<script>console.log('DEBUG: Transaction started');</script>";

                // Get product info
                $productQuery = $this->db->query("SELECT Name, Preis FROM produkte WHERE `Produkt-Nr` = ?", [$productId]);
                $product = $productQuery->getRow();
                
                if (!$product) {
                    throw new \Exception('Produkt nicht gefunden.');
                }
                
                echo "<script>console.log('DEBUG: Product found: " . addslashes($product->Name) . ", Price: " . $product->Preis . "');</script>";

                // Calculate prices
                $retailPrice = (float)$product->Preis;
                $wholesalePrice = $retailPrice * 0.5;
                $totalPrice = $quantity * $wholesalePrice;
                
                echo "<script>console.log('DEBUG: Calculated prices - Retail: $retailPrice, Wholesale: $wholesalePrice, Total: $totalPrice');</script>";

                // Get new Einkauf-Nr
                $einkaufQuery = $this->db->query("SELECT MAX(`Einkauf-Nr`) as max_id FROM einkaeufe");
                $einkaufResult = $einkaufQuery->getRow();
                $newEinkaufNr = ($einkaufResult && $einkaufResult->max_id) ? $einkaufResult->max_id + 1 : 1;
                
                echo "<script>console.log('DEBUG: New Einkauf-Nr: $newEinkaufNr');</script>";

                // Insert into einkaeufe
                $insertEinkauf = $this->db->query(
                    "INSERT INTO einkaeufe (`Einkauf-Nr`, `Gesamtmenge`, `Gesamtpreis`, `Mitarbeiter-Nr`, `Datum`) VALUES (?, ?, ?, ?, NOW())",
                    [$newEinkaufNr, $quantity, $totalPrice, $employeeId]
                );
                
                echo "<script>console.log('DEBUG: Einkaeufe insert result: " . ($insertEinkauf ? 'SUCCESS' : 'FAILED') . "');</script>";
                if (!$insertEinkauf) {
                    $dbError = $this->db->error();
                    echo "<script>console.log('DEBUG: Einkaeufe insert error: " . json_encode($dbError) . "');</script>";
                }

                // Get new transaction ID for ek-transaktionen
                $transactionQuery = $this->db->query("SELECT MAX(id) as max_id FROM `ek-transaktionen`");
                $transactionResult = $transactionQuery->getRow();
                $newTransactionId = ($transactionResult && $transactionResult->max_id) ? $transactionResult->max_id + 1 : 1;
                
                echo "<script>console.log('DEBUG: New Transaction ID: $newTransactionId');</script>";

                // Insert into ek-transaktionen (Transaktion-Nr should match Einkauf-Nr)
                $insertTransaction = $this->db->query(
                    "INSERT INTO `ek-transaktionen` (`id`, `Transaktion-Nr`, `Produkt-Nr`, `Menge`, `Gesamtpreis`) VALUES (?, ?, ?, ?, ?)",
                    [$newTransactionId, $newEinkaufNr, $productId, $quantity, $totalPrice]
                );
                
                echo "<script>console.log('DEBUG: ek-transaktionen insert result: " . ($insertTransaction ? 'SUCCESS' : 'FAILED') . "');</script>";
                if (!$insertTransaction) {
                    $dbError = $this->db->error();
                    echo "<script>console.log('DEBUG: ek-transaktionen insert error: " . json_encode($dbError) . "');</script>";
                }

                // Complete transaction
                $this->db->transComplete();
                
                echo "<script>console.log('DEBUG: Transaction completed');</script>";

                if ($this->db->transStatus() === false) {
                    // Get the actual database error
                    $error = $this->db->error();
                    echo "<script>console.log('DEBUG: Transaction failed: " . json_encode($error) . "');</script>";
                    throw new \Exception('Datenbankfehler: ' . $error['message']);
                }
                
                echo "<script>console.log('DEBUG: Transaction successful!');</script>";

                $success_message = sprintf(
                    'Einkauf erfolgreich! Produkt: %s, Menge: %d, Gesamtpreis: %.2f€, Einkauf-Nr: %d',
                    $product->Name,
                    $quantity,
                    $totalPrice,
                    $newEinkaufNr
                );
                
                echo "<script>console.log('DEBUG: Success message set: " . addslashes($success_message) . "');</script>";

            } catch (\Exception $e) {
                echo "<script>console.log('DEBUG: Exception caught: " . addslashes($e->getMessage()) . "');</script>";
                $error_message = $e->getMessage();
            }
        } else {
            echo "<script>console.log('DEBUG: Not a POST request or action mismatch. Method: " . $this->request->getMethod() . ", Action: " . ($this->request->getPost('action') ?? 'NULL') . "');</script>";
            echo "<script>console.log('DEBUG: Method check: " . (strtolower($this->request->getMethod()) === 'post' ? 'TRUE' : 'FALSE') . "');</script>";
            echo "<script>console.log('DEBUG: Action check: " . ($this->request->getPost('action') === 'purchase_stock' ? 'TRUE' : 'FALSE') . "');</script>";
        }

        try {
            // Get dashboard data
            $stats = $this->getDashboardStats();
            $recentSales = $this->getRecentSales();
            $inventoryStatus = $this->getInventoryStatus();
            $lowStockProducts = $this->getLowStockProducts();
            $allProducts = $this->getAllProducts();
            $allEmployees = $this->getAllEmployees();
            
            $selectedEmployeeId = $this->request->getGet('employee_id') ?? session()->get('mitarbeiter_id');
            $employeePurchases = $this->getEmployeePurchases($selectedEmployeeId);

            $data = [
                'mitarbeiter_name' => session()->get('mitarbeiter_name'),
                'mitarbeiter_id' => session()->get('mitarbeiter_id'),
                'stats' => $stats,
                'recent_sales' => $recentSales,
                'inventory_status' => $inventoryStatus,
                'low_stock_products' => $lowStockProducts,
                'all_products' => $allProducts,
                'all_employees' => $allEmployees,
                'employee_purchases' => $employeePurchases,
                'selected_employee_id' => $selectedEmployeeId,
                'success_message' => $success_message,
                'error_message' => $error_message
            ];

            return view('dashboard/index', $data);

        } catch (\Exception $e) {
            $data = [
                'mitarbeiter_name' => session()->get('mitarbeiter_name'),
                'mitarbeiter_id' => session()->get('mitarbeiter_id'),
                'error' => $e->getMessage(),
                'stats' => ['total_sales' => 0, 'total_revenue' => 0, 'orders_today' => 0, 'low_stock_count' => 0],
                'recent_sales' => [],
                'inventory_status' => [],
                'low_stock_products' => [],
                'all_products' => [],
                'all_employees' => [],
                'employee_purchases' => [],
                'selected_employee_id' => null,
                'success_message' => $success_message,
                'error_message' => $error_message
            ];

            return view('dashboard/index', $data);
        }
    }

    public function getProductPrice()
    {
        if (!session()->get('mitarbeiter_logged_in')) {
            return $this->response->setJSON(['error' => 'Nicht angemeldet']);
        }

        $productId = $this->request->getPost('product_id');
        
        if (!$productId) {
            return $this->response->setJSON(['error' => 'Keine Produkt-ID angegeben']);
        }

        try {
            $productQuery = $this->db->query("SELECT Name, Preis FROM produkte WHERE `Produkt-Nr` = ?", [$productId]);
            $product = $productQuery->getRow();
            
            if (!$product) {
                return $this->response->setJSON(['error' => 'Produkt nicht gefunden']);
            }

            $retailPrice = (float)$product->Preis;
            $wholesalePrice = $retailPrice * 0.5;

            return $this->response->setJSON([
                'success' => true,
                'product_name' => $product->Name,
                'retail_price' => $retailPrice,
                'wholesale_price' => $wholesalePrice,
                'discount_percent' => 50
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
        }
    }

    private function getDashboardStats()
    {
        $totalSalesQuery = $this->db->query("SELECT COUNT(*) as count FROM verkaeufe");
        $totalSales = $totalSalesQuery->getRow()->count;

        $totalRevenueQuery = $this->db->query("SELECT COALESCE(SUM(Preis), 0) as total FROM verkaeufe");
        $totalRevenue = $totalRevenueQuery->getRow()->total;

        $ordersToday = $this->db->query("SELECT COUNT(*) as count FROM verkaeufe WHERE DATE(Datum) = CURDATE()")->getRow()->count;

        $lowStockQuery = $this->db->query("
            SELECT COUNT(*) as count FROM (
                SELECT p.`Produkt-Nr`,
                       COALESCE(purchases.total, 0) - COALESCE(sales.total, 0) as stock
                FROM produkte p
                LEFT JOIN (
                    SELECT `Produkt-Nr`, SUM(Menge) as total 
                    FROM `ek-transaktionen` 
                    GROUP BY `Produkt-Nr`
                ) purchases ON p.`Produkt-Nr` = purchases.`Produkt-Nr`
                LEFT JOIN (
                    SELECT `Produkt-Nr`, SUM(Menge) as total 
                    FROM `vk-transaktionen` 
                    GROUP BY `Produkt-Nr`
                ) sales ON p.`Produkt-Nr` = sales.`Produkt-Nr`
                HAVING stock <= 5
            ) low_stock
        ");
        $lowStockCount = $lowStockQuery->getRow()->count;

        return [
            'total_sales' => $totalSales,
            'total_revenue' => $totalRevenue,
            'orders_today' => $ordersToday,
            'low_stock_count' => $lowStockCount
        ];
    }

    private function getRecentSales()
    {
        $query = $this->db->query("
            SELECT `Verkauf-Nr`, `Gesamtmenge`, `Preis`, `Datum`
            FROM verkaeufe 
            ORDER BY Datum DESC 
            LIMIT 10
        ");
        
        return $query->getResultArray();
    }

    private function getInventoryStatus()
    {
        $query = $this->db->query("
            SELECT p.Name, p.`Produkt-Nr`,
                   COALESCE(purchases.total, 0) - COALESCE(sales.total, 0) as current_stock
            FROM produkte p
            LEFT JOIN (
                SELECT `Produkt-Nr`, SUM(Menge) as total 
                FROM `ek-transaktionen` 
                GROUP BY `Produkt-Nr`
            ) purchases ON p.`Produkt-Nr` = purchases.`Produkt-Nr`
            LEFT JOIN (
                SELECT `Produkt-Nr`, SUM(Menge) as total 
                FROM `vk-transaktionen` 
                GROUP BY `Produkt-Nr`
            ) sales ON p.`Produkt-Nr` = sales.`Produkt-Nr`
            ORDER BY current_stock DESC
        ");
        
        return $query->getResultArray();
    }

    private function getLowStockProducts()
    {
        $query = $this->db->query("
            SELECT p.Name, p.`Produkt-Nr`,
                   COALESCE(purchases.total, 0) - COALESCE(sales.total, 0) as current_stock
            FROM produkte p
            LEFT JOIN (
                SELECT `Produkt-Nr`, SUM(Menge) as total 
                FROM `ek-transaktionen` 
                GROUP BY `Produkt-Nr`
            ) purchases ON p.`Produkt-Nr` = purchases.`Produkt-Nr`
            LEFT JOIN (
                SELECT `Produkt-Nr`, SUM(Menge) as total 
                FROM `vk-transaktionen` 
                GROUP BY `Produkt-Nr`
            ) sales ON p.`Produkt-Nr` = sales.`Produkt-Nr`
            HAVING current_stock <= 5
            ORDER BY current_stock ASC
            LIMIT 10
        ");
        
        return $query->getResultArray();
    }

    private function getAllProducts()
    {
        $query = $this->db->query("
            SELECT `Produkt-Nr`, Name, Preis
            FROM produkte 
            ORDER BY Name ASC
        ");
        
        return $query->getResultArray();
    }

    private function getAllEmployees()
    {
        $query = $this->db->query("
            SELECT `Mitarbeiter-Nr`, Name
            FROM mitarbeiter 
            ORDER BY Name ASC
        ");
        
        return $query->getResultArray();
    }

    private function getEmployeePurchases($employeeId)
    {
        if (!$employeeId) {
            return [];
        }

        $query = $this->db->query("
            SELECT e.`Einkauf-Nr`, 
                   e.`Gesamtmenge`, 
                   e.`Gesamtpreis`, 
                   e.`Datum`,
                   et.`Produkt-Nr`,
                   et.`Menge` as einzelmenge,
                   et.`Gesamtpreis` as einzelpreis,
                   p.Name as produktname,
                   p.Preis as verkaufspreis
            FROM einkaeufe e
            LEFT JOIN `ek-transaktionen` et ON e.`Einkauf-Nr` = et.`Transaktion-Nr`
            LEFT JOIN produkte p ON et.`Produkt-Nr` = p.`Produkt-Nr`
            WHERE e.`Mitarbeiter-Nr` = ?
            ORDER BY e.Datum DESC
            LIMIT 20
        ", [$employeeId]);
        
        return $query->getResultArray();
    }
} 