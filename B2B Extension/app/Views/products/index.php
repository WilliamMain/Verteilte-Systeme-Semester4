<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marko Marko B2B - Produkte</title>
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

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            max-width: 1400px;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .product-name {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .product-price {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .product-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .quantity-input {
            width: 60px;
            padding: 8px 10px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .quantity-input:focus {
            outline: none;
            border-color: #007bff;
        }

        .add-to-cart-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            flex: 1;
        }

        .add-to-cart-btn:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }

        .add-to-cart-btn:active {
            transform: translateY(0);
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

            .products-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .page-title {
                font-size: 24px;
            }
        }

        /* Loading State */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Success Message */
        .alert {
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
                    <a href="<?= base_url('/') ?>" class="nav-link active">Home</a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('cart') ?>" class="nav-link">Warenkorb</a>
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
                <h1 class="page-title">Produkte</h1>
            </div>

            <!-- Success Alert -->
            <div id="success-alert" class="alert alert-success">
                Produkt wurde erfolgreich zum Warenkorb hinzugefügt!
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card" 
                     data-id="<?= $product['id'] ?>" 
                     data-name="<?= esc($product['name']) ?>" 
                     data-price="<?= preg_replace('/[^0-9.]/', '', $product['price']) ?>">
                    <h3 class="product-name"><?= esc($product['name']) ?></h3>
                    <p class="product-price">Preis: <?= number_format((float)preg_replace('/[^0-9.]/', '', $product['price']), 2, ',', '.') ?> <?= esc($product['currency']) ?></p>
                    
                    <div class="product-actions">
                        <input type="number" 
                               class="quantity-input qty" 
                               value="1" 
                               min="1" 
                               id="quantity-<?= $product['id'] ?>">
                        
                        <button class="add-to-cart-btn" 
                                onclick="addProductToCart(<?= $product['id'] ?>)">
                            In den Warenkorb
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
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

        function addToCart(product) {
            let cart = getCart();
            const existing = cart.find(p => p.id === product.id);
            if (existing) {
                existing.qty += product.qty;
            } else {
                cart.push(product);
            }
            saveCart(cart);
            
            // Show success message
            const alert = document.getElementById('success-alert');
            alert.style.display = 'block';
            
            // Hide after 3 seconds
            setTimeout(() => {
                alert.style.display = 'none';
            }, 3000);
        }

        function addProductToCart(productId) {
            const productCard = document.querySelector(`[data-id="${productId}"]`);
            const quantity = parseInt(document.getElementById(`quantity-${productId}`).value);
            const button = event.target;
            
            // Add loading state
            button.classList.add('loading');
            button.textContent = 'Wird hinzugefügt...';
            
            try {
                const product = {
                    id: parseInt(productCard.dataset.id),
                    name: productCard.dataset.name,
                    price: parseFloat(productCard.dataset.price),
                    qty: quantity
                };

                console.log('Adding product to cart:', product); // Debug

                if (quantity > 0) {
                    addToCart(product);
                    
                    // Reset quantity to 1
                    document.getElementById(`quantity-${productId}`).value = 1;
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                alert('Fehler beim Hinzufügen zum Warenkorb. Bitte versuchen Sie es erneut.');
            } finally {
                // Remove loading state
                setTimeout(() => {
                    button.classList.remove('loading');
                    button.textContent = 'In den Warenkorb';
                }, 500);
            }
        }

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to product cards
            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.borderColor = '#007bff';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.borderColor = '#e9ecef';
                });
            });
        });
    </script>
</body>
</html>