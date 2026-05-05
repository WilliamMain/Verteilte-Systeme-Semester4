<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitarbeiter Dashboard - Marko Marko B2B</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #4a4a6a 0%, #3a3a5a 100%);
            color: white;
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 25px 20px;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h1 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
        }

        .nav-item {
            margin: 5px 0;
        }

        .nav-link {
            display: block;
            padding: 15px 25px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            border-left-color: #007bff;
            color: white;
        }

        .nav-link.active {
            background: rgba(255,255,255,0.15);
        }

        .logout-btn {
            background: #dc3545;
            margin: 20px;
            text-align: center;
            border-radius: 6px;
        }

        .logout-btn:hover {
            background: #c82333;
            border-left-color: #dc3545;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .welcome-text {
            color: #6c757d;
            font-size: 16px;
            margin-top: 5px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-left: 4px solid;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.sales {
            border-left-color: #28a745;
        }

        .stat-card.revenue {
            border-left-color: #007bff;
        }

        .stat-card.today {
            border-left-color: #ffc107;
        }

        .stat-card.stock {
            border-left-color: #dc3545;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-card.sales .stat-number { color: #28a745; }
        .stat-card.revenue .stat-number { color: #007bff; }
        .stat-card.today .stat-number { color: #ffc107; }
        .stat-card.stock .stat-number { color: #dc3545; }

        .stat-label {
            color: #6c757d;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Data Tables */
        .dashboard-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #e9ecef;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* Grid Layout for Tables */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        /* Purchase Form Styles */
        .purchase-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #1e7e34;
        }

        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 12px;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 15px;
            align-items: end;
        }

        /* Employee Filter */
        .employee-filter {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 15px;
            align-items: end;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 40px;
        }

        /* Price Breakdown Display */
        .price-breakdown {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            border-left: 4px solid #28a745;
        }

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .breakdown-item:last-child {
            border-bottom: none;
        }

        .breakdown-item.total {
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid #28a745;
            font-size: 18px;
            font-weight: bold;
        }

        .breakdown-item .label {
            color: #495057;
            font-weight: 500;
        }

        .breakdown-item .value {
            font-weight: 600;
            color: #2c3e50;
        }

        .breakdown-item .value.highlight {
            color: #28a745;
            font-size: 16px;
        }

        .breakdown-item .value.total-value {
            color: #007bff;
            font-size: 20px;
        }

        #price-display {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #28a745;
        }

        /* Status badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-ausverkauft {
            background: #f8d7da;
            color: #721c24;
        }

        .status-kritisch {
            background: #f8d7da;
            color: #721c24;
        }

        .status-niedrig {
            background: #fff3cd;
            color: #856404;
        }

        .status-verfuegbar {
            background: #d4edda;
            color: #155724;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .page-title {
                font-size: 24px;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        .stat-card {
            animation: fadeInUp 0.6s ease-out;
        }

        .dashboard-section {
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h1>Marko Marko B2B</h1>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?= base_url('/') ?>" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('cart') ?>" class="nav-link">Warenkorb</a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('mitarbeiter-dashboard') ?>" class="nav-link active">Mitarbeiter</a>
                </li>
                <li class="nav-item">
                    <a href="http://localhost/inventory/WilliyRollerB2C/html/index.html" class="nav-link">Zum B2C Shop ↗</a>
                </li>
            </ul>
            <div class="nav-item">
                <a href="<?= base_url('auth/logout') ?>" class="nav-link logout-btn">Abmelden</a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <div>
                    <h1 class="page-title">Dashboard</h1>
                    <p class="welcome-text">Willkommen zurück, <?= esc($mitarbeiter_name ?? 'Mitarbeiter') ?>!</p>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                ✅ <?= esc($success_message) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                ❌ <?= esc($error_message) ?>
            </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
            <div class="alert alert-error">
                ⚠️ Fehler beim Laden der Dashboard-Daten: <?= esc($error) ?>
            </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card sales">
                    <div class="stat-number"><?= number_format($stats['total_sales'] ?? 0) ?></div>
                    <div class="stat-label">Gesamte Verkäufe</div>
                </div>
                
                <div class="stat-card revenue">
                    <div class="stat-number"><?= number_format($stats['total_revenue'] ?? 0, 2, ',', '.') ?> €</div>
                    <div class="stat-label">Gesamtumsatz</div>
                </div>
                
                <div class="stat-card today">
                    <div class="stat-number"><?= number_format($stats['orders_today'] ?? 0) ?></div>
                    <div class="stat-label">Bestellungen Heute</div>
                </div>
                
                <div class="stat-card stock">
                    <div class="stat-number"><?= number_format($stats['low_stock_count'] ?? 0) ?></div>
                    <div class="stat-label">Niedrige Lagerbestände</div>
                </div>
            </div>

            <!-- Stock Purchase Section -->
            <div class="dashboard-section">
                <h2 class="section-title">🛒 Lager Einkauf (B2B - 50% Rabatt)</h2>
                
                <form action="<?= base_url('mitarbeiter-dashboard') ?>" method="POST" id="purchase-form">
                    <div class="purchase-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="product_id">Produkt auswählen</label>
                                <select name="product_id" id="product_id" class="form-control" required>
                                    <option value="">-- Produkt wählen --</option>
                                    <?php if (!empty($all_products)): ?>
                                        <?php foreach ($all_products as $product): ?>
                                        <option value="<?= esc($product['Produkt-Nr']) ?>" 
                                                data-retail-price="<?= esc($product['Preis']) ?>"
                                                data-product-name="<?= esc($product['Name']) ?>">
                                            <?= esc($product['Name']) ?> - Verkaufspreis: <?= number_format($product['Preis'], 2, ',', '.') ?>€
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="quantity">Menge</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" 
                                       min="1" required placeholder="z.B. 10">
                            </div>
                            
                            <div class="form-group">
                                <label>B2B Einkaufspreis</label>
                                <div id="price-display" class="form-control" style="background: #e9ecef; border: 2px solid #28a745;">
                                    <span style="color: #6c757d;">Wählen Sie ein Produkt aus</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Price Breakdown Display -->
                        <div id="price-breakdown" class="price-breakdown" style="display: none;">
                            <div class="breakdown-item">
                                <span class="label">Verkaufspreis:</span>
                                <span id="retail-price-display" class="value">-</span>
                            </div>
                            <div class="breakdown-item">
                                <span class="label">B2B Einkaufspreis (50% Rabatt):</span>
                                <span id="wholesale-price-display" class="value highlight">-</span>
                            </div>
                            <div class="breakdown-item total">
                                <span class="label">Gesamtpreis:</span>
                                <span id="total-price-display" class="value total-value">-</span>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top: 20px;">
                            <input type="hidden" name="action" value="purchase_stock">
                            <button type="submit" class="btn btn-success" id="submit-btn" disabled>
                                📦 Einkauf durchführen
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Recent Sales -->
                <div class="dashboard-section">
                    <h2 class="section-title">💰 Aktuelle Verkäufe</h2>
                    <?php if (!empty($recent_sales)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Verkauf-Nr</th>
                                <th>Menge</th>
                                <th>Preis</th>
                                <th>Datum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_sales as $sale): ?>
                            <tr>
                                <td><?= esc($sale['Verkauf-Nr']) ?></td>
                                <td><?= esc($sale['Gesamtmenge']) ?></td>
                                <td><?= number_format($sale['Preis'], 2, ',', '.') ?> €</td>
                                <td><?= date('d.m.Y H:i', strtotime($sale['Datum'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">Keine aktuellen Verkäufe vorhanden</div>
                    <?php endif; ?>
                </div>

                <!-- Inventory Status -->
                <div class="dashboard-section">
                    <h2 class="section-title">📦 Lagerbestand</h2>
                    <?php if (!empty($inventory_status)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produkt-Nr</th>
                                <th>Produktname</th>
                                <th>Lagerbestand</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory_status as $product): ?>
                            <tr>
                                <td><?= esc($product['Produkt-Nr']) ?></td>
                                <td><?= esc($product['Name']) ?></td>
                                <td>
                                    <span style="<?= ($product['current_stock'] <= 5) ? 'color: #dc3545; font-weight: bold;' : 'color: #28a745; font-weight: 500;' ?>">
                                        <?= number_format($product['current_stock']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($product['current_stock'] <= 0): ?>
                                        <span class="status-badge status-ausverkauft">❌ Ausverkauft</span>
                                    <?php elseif ($product['current_stock'] <= 2): ?>
                                        <span class="status-badge status-kritisch">⚠️ Kritisch</span>
                                    <?php elseif ($product['current_stock'] <= 5): ?>
                                        <span class="status-badge status-niedrig">⚡ Niedrig</span>
                                    <?php else: ?>
                                        <span class="status-badge status-verfuegbar">✅ Verfügbar</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">Keine Lagerbestände vorhanden</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <?php if (!empty($low_stock_products)): ?>
            <div class="dashboard-section">
                <h2 class="section-title">⚠️ Niedrige Lagerbestände</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Produkt-Nr</th>
                            <th>Produktname</th>
                            <th>Aktueller Bestand</th>
                            <th>Status</th>
                            <th>Schnellaktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock_products as $product): ?>
                        <tr>
                            <td><?= esc($product['Produkt-Nr']) ?></td>
                            <td><?= esc($product['Name']) ?></td>
                            <td><?= number_format($product['current_stock']) ?></td>
                            <td>
                                <?php if ($product['current_stock'] <= 0): ?>
                                    <span class="status-badge status-ausverkauft">Ausverkauft</span>
                                <?php elseif ($product['current_stock'] <= 2): ?>
                                    <span class="status-badge status-kritisch">Kritisch</span>
                                <?php else: ?>
                                    <span class="status-badge status-niedrig">Niedrig</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="fillPurchaseForm(<?= esc($product['Produkt-Nr']) ?>, '<?= esc($product['Name']) ?>')" 
                                        class="btn btn-primary btn-sm">
                                    🛒 Nachbestellen
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate numbers counting up
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(element => {
                const finalValue = element.textContent.replace(/[^\d.-]/g, '');
                if (finalValue && !isNaN(finalValue)) {
                    animateNumber(element, 0, parseInt(finalValue), 1000);
                }
            });

            // Add click effects to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-5px)';
                    }, 150);
                });
            });

            // Form validation and price calculation
            const purchaseForm = document.getElementById('purchase-form');
            const productSelect = document.getElementById('product_id');
            const quantityInput = document.getElementById('quantity');
            const priceDisplay = document.getElementById('price-display');
            const priceBreakdown = document.getElementById('price-breakdown');
            const submitBtn = document.getElementById('submit-btn');

            // Price display elements
            const retailPriceDisplay = document.getElementById('retail-price-display');
            const wholesalePriceDisplay = document.getElementById('wholesale-price-display');
            const totalPriceDisplay = document.getElementById('total-price-display');

            let currentProduct = null;

            function updatePriceDisplay() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const quantity = parseInt(quantityInput.value) || 0;

                if (selectedOption.value && selectedOption.dataset.retailPrice) {
                    const retailPrice = parseFloat(selectedOption.dataset.retailPrice);
                    const wholesalePrice = retailPrice * 0.5;
                    const totalPrice = wholesalePrice * quantity;

                    currentProduct = {
                        name: selectedOption.dataset.productName,
                        retailPrice: retailPrice,
                        wholesalePrice: wholesalePrice
                    };

                    // Update price display
                    priceDisplay.innerHTML = `<strong>${wholesalePrice.toFixed(2)}€ pro Stück</strong>`;
                    
                    // Update breakdown
                    retailPriceDisplay.textContent = `${retailPrice.toFixed(2)}€`;
                    wholesalePriceDisplay.textContent = `${wholesalePrice.toFixed(2)}€`;
                    
                    if (quantity > 0) {
                        totalPriceDisplay.textContent = `${totalPrice.toFixed(2)}€`;
                        submitBtn.disabled = false;
                    } else {
                        totalPriceDisplay.textContent = '-';
                        submitBtn.disabled = true;
                    }
                    
                    priceBreakdown.style.display = 'block';
                } else {
                    priceDisplay.innerHTML = '<span style="color: #6c757d;">Wählen Sie ein Produkt aus</span>';
                    priceBreakdown.style.display = 'none';
                    submitBtn.disabled = true;
                    currentProduct = null;
                }
            }

            productSelect.addEventListener('change', updatePriceDisplay);
            quantityInput.addEventListener('input', updatePriceDisplay);

            if (purchaseForm) {
                purchaseForm.addEventListener('submit', function(e) {
                    const quantity = parseInt(quantityInput.value) || 0;
                    
                    if (!currentProduct || quantity <= 0) {
                        e.preventDefault();
                        alert('Bitte wählen Sie ein Produkt und geben Sie eine gültige Menge ein.');
                        return false;
                    }
                    
                    const totalPrice = currentProduct.wholesalePrice * quantity;
                    const savings = (currentProduct.retailPrice - currentProduct.wholesalePrice) * quantity;
                    
                    const confirmMsg = `🛒 Einkauf bestätigen:\n\n` +
                        `📦 Produkt: ${currentProduct.name}\n` +
                        `🔢 Menge: ${quantity} Stück\n` +
                        `📊 Gesamtpreis: ${totalPrice.toFixed(2)}€\n` +
                        `Möchten Sie den Einkauf durchführen?`;
                    
                    if (!confirm(confirmMsg)) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });

        function animateNumber(element, start, end, duration) {
            const range = end - start;
            const increment = range / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= end) {
                    current = end;
                    clearInterval(timer);
                }
                
                const formatted = Math.floor(current).toLocaleString('de-DE');
                const originalText = element.textContent;
                const suffix = originalText.replace(/[\d.,\s]/g, '');
                element.textContent = formatted + suffix;
            }, 16);
        }

        // Quick fill purchase form from low stock table
        function fillPurchaseForm(productId, productName) {
            const productSelect = document.getElementById('product_id');
            const quantityInput = document.getElementById('quantity');
            
            // Set the product
            productSelect.value = productId;
            
            // Trigger change event to update prices
            productSelect.dispatchEvent(new Event('change'));
            
            // Suggest a reasonable quantity
            quantityInput.value = 10;
            
            // Trigger input event to update total
            quantityInput.dispatchEvent(new Event('input'));
            
            // Focus on quantity field
            quantityInput.focus();
            quantityInput.select();
            
            // Scroll to purchase form
            document.querySelector('.purchase-form').scrollIntoView({ 
                behavior: 'smooth',
                block: 'center'
            });
            
            // Highlight the form briefly
            const form = document.querySelector('.purchase-form');
            form.style.boxShadow = '0 0 20px rgba(0, 123, 255, 0.3)';
            form.style.transform = 'scale(1.02)';
            
            setTimeout(() => {
                form.style.boxShadow = '';
                form.style.transform = '';
            }, 2000);
            
            // Show confirmation
            setTimeout(() => {
                alert(`✅ Produkt "${productName}" wurde ausgewählt!\n\n📝 Überprüfen Sie die Menge und bestätigen Sie den Einkauf.\n💰 Der B2B-Preis (50% Rabatt) wird automatisch berechnet.`);
            }, 500);
        }

        // Auto-refresh dashboard data every 5 minutes
        setTimeout(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html>