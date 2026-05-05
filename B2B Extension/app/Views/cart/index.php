<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warenkorb - Marko Marko B2B</title>
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

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        /* Messages */
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Cart Table */
        .cart-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .cart-table th {
            background: #f8f9fa;
            padding: 20px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #e9ecef;
        }

        .cart-table td {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .cart-table tr:last-child td {
            border-bottom: none;
        }

        .cart-table tr:hover {
            background: #f8f9fa;
        }

        .empty-cart-message {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 40px !important;
        }

        /* Cart Summary */
        .cart-summary {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            text-align: right;
        }

        .cart-summary {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
        }

        #total {
            color: #007bff;
            font-size: 24px;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-remove {
            background: #dc3545;
            color: white;
        }

        .btn-remove:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .checkout-button {
            background: #28a745;
            color: white;
            padding: 15px 40px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .checkout-button:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .checkout-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }

            .cart-table {
                font-size: 14px;
            }

            .cart-table th,
            .cart-table td {
                padding: 15px 10px;
            }

            .page-title {
                font-size: 24px;
            }
        }

        /* Loading state */
        .loading {
            opacity: 0.6;
            pointer-events: none;
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
                    <a href="<?= base_url('cart') ?>" class="nav-link active">Warenkorb</a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('login') ?>" class="nav-link">Mitarbeiter</a>
                </li>
                <li class="nav-item">
                    <a href="http://localhost/inventory/WilliyRollerB2C/html/index.html" class="nav-link">Zum B2C Shop ↗</a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Warenkorb</h1>
            </div>

            <?php if (!empty($success_message)): ?>
            <div class="message success-message">
                <?= esc($success_message) ?>
            </div>
            <script>
                // Clear localStorage on successful order
                localStorage.removeItem('cart');
            </script>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="message error-message">
                Fehler: <?= esc($error_message) ?>
            </div>
            <?php endif; ?>

            <form id="checkout-form" method="post" action="<?= base_url('cart/checkout') ?>">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Preis</th>
                            <th>Menge</th>
                            <th>Zwischensumme</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody id="cart-items">
                        <!-- Cart Items will be populated by JavaScript -->
                        <tr>
                            <td colspan="5" class="empty-cart-message">Ihr Warenkorb ist leer.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="cart-summary">
                    Gesamtsumme: <span id="total">0.00</span> €
                </div>

                <input type="hidden" id="cartDataInput" name="cartData" value="">
                <button type="submit" class="checkout-button" id="checkout-btn" disabled>Bestellen</button>
            </form>
        </main>
    </div>

    <script>
        // Cart functionality
        function getCart() {
            const cart = localStorage.getItem("cart");
            return cart ? JSON.parse(cart) : [];
        }

        function saveCart(cart) {
            localStorage.setItem("cart", JSON.stringify(cart));
        }

        function removeFromCart(productId) {
            let cart = getCart();
            cart = cart.filter(p => p.id !== productId);
            saveCart(cart);
            renderCart();
        }

        function renderCart() {
            const cart = getCart();
            const container = document.getElementById("cart-items");
            const totalSpan = document.getElementById("total");
            const cartDataInput = document.getElementById("cartDataInput");
            const checkoutBtn = document.getElementById("checkout-btn");

            container.innerHTML = "";

            if (!cart.length) {
                container.innerHTML = `<tr><td colspan="5" class="empty-cart-message">Ihr Warenkorb ist leer.</td></tr>`;
                totalSpan.textContent = "0.00";
                cartDataInput.value = "";
                checkoutBtn.disabled = true;
                return;
            }

            let total = 0;
            cart.forEach(item => {
                const subtotal = item.qty * item.price;
                total += subtotal;

                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td>${item.price.toFixed(2)} €</td>
                    <td>${item.qty}</td>
                    <td>${subtotal.toFixed(2)} €</td>
                    <td><button type="button" class="btn btn-remove" onclick="removeFromCart(${item.id})">Entfernen</button></td>
                `;
                container.appendChild(row);
            });

            totalSpan.textContent = total.toFixed(2);
            cartDataInput.value = JSON.stringify(cart);
            checkoutBtn.disabled = false;
        }

        // Form submission handling
        document.getElementById('checkout-form').addEventListener('submit', function(event) {
            const cart = getCart();
            if (!cart.length) {
                event.preventDefault();
                alert("Ihr Warenkorb ist leer. Bitte fügen Sie Produkte hinzu, bevor Sie bestellen.");
                return;
            }

            // Add loading state
            const btn = document.getElementById('checkout-btn');
            btn.classList.add('loading');
            btn.textContent = 'Wird bestellt...';
        });

        // Initialize cart on page load
        document.addEventListener('DOMContentLoaded', function() {
            renderCart();
        });
    </script>
</body>
</html>