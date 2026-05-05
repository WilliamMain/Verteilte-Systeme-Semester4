// B2B API KONFIGURATION
const B2B_API_CONFIG = {
    baseUrl: 'http://localhost/inventory/MarkoMarkoProjekt/public',
    apiKey: 'b2c_to_b2b_secret_key_2024'
};

// CORRECTED PRODUKT-MAPPING: B2C Namen zu B2B Produkt-IDs
const PRODUCT_MAPPING = {
    // Fixed product names to match your HTML exactly
    'nicht so e-roller': 1,
    'disco roller': 2,
    'e-roller schwarz': 3,          // Was "e-roller ultron" in HTML
    'e-roller wlan': 4,
    'e-roller us edition': 5,
    'e-roller x2': 6,
    'e-roller v8': 7,               // Was wrongly mapped to "E-Roller WLAN" in product7 button
    'kennzeichenhalter': 8,
    'reifen': 9,
    'ladekabel': 10
};

// Stock cache to avoid repeated API calls
let stockCache = {};
let stockCacheExpiry = {};
const CACHE_DURATION = 30000; // 30 seconds

// Check stock for a product
async function checkProductStock(productId) {
    const now = Date.now();
    
    // Return cached result if still valid
    if (stockCache[productId] && stockCacheExpiry[productId] > now) {
        console.log(`📋 Using cached stock for product ${productId}:`, stockCache[productId]);
        return stockCache[productId];
    }
    
    try {
        console.log(`🔍 Checking stock for product ${productId}`);
        
        const response = await fetch(`${B2B_API_CONFIG.baseUrl}/api/check-stock`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: parseInt(productId)
            })
        });
        
        console.log(`📡 Stock API response status: ${response.status}`);
        
        if (!response.ok) {
            console.warn(`⚠️ Stock API returned ${response.status}, allowing optimistic add`);
            // Return optimistic result
            return {
                success: true,
                current_stock: 999,
                available: true,
                error: true,
                message: 'Stock check failed, proceeding optimistically'
            };
        }
        
        const data = await response.json();
        console.log(`📦 Stock check result for product ${productId}:`, data);
        
        // Cache the result
        stockCache[productId] = data;
        stockCacheExpiry[productId] = now + CACHE_DURATION;
        
        return data;
    } catch (error) {
        console.error(`❌ Stock check failed for product ${productId}:`, error);
        // Return optimistic result if API fails
        return {
            success: true,
            current_stock: 999,
            available: true,
            error: true,
            message: 'Stock check failed, proceeding optimistically'
        };
    }
}

// Show stock status message
function showStockMessage(productName, message, isError = false) {
    console.log(`📢 Showing message for ${productName}: ${message} (Error: ${isError})`);
    
    // Remove any existing messages first
    const existingMessages = document.querySelectorAll('.stock-notification');
    existingMessages.forEach(msg => msg.remove());
    
    // Create a temporary message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `stock-notification alert ${isError ? 'alert-warning' : 'alert-success'}`;
    messageDiv.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 10001;
        max-width: 400px;
        font-size: 14px;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        background-color: ${isError ? '#fff3cd' : '#d1edff'};
        border: 1px solid ${isError ? '#ffeaa7' : '#bee5eb'};
        color: ${isError ? '#856404' : '#0c5460'};
        animation: slideIn 0.3s ease-out;
    `;
    
    // Add CSS animation if not already present
    if (!document.querySelector('#stock-message-styles')) {
        const styleSheet = document.createElement('style');
        styleSheet.id = 'stock-message-styles';
        styleSheet.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            .stock-notification.removing {
                animation: slideOut 0.3s ease-in forwards;
            }
        `;
        document.head.appendChild(styleSheet);
    }
    
    messageDiv.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <i class="fas ${isError ? 'fa-exclamation-triangle' : 'fa-check-circle'}" style="margin-right: 8px;"></i>
                <strong>${productName}:</strong> ${message}
            </div>
            <button type="button" onclick="this.parentElement.parentElement.remove()" 
                    style="background: none; border: none; font-size: 18px; cursor: pointer; color: inherit; margin-left: 10px;">
                ×
            </button>
        </div>
    `;
    
    document.body.appendChild(messageDiv);
    
    // Auto-remove after 6 seconds with animation
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.classList.add('removing');
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.remove();
                }
            }, 300);
        }
    }, 6000);
}

// B2B API Client für B2C Integration
class B2BApiClient {
    constructor(baseUrl, apiKey = null) {
        this.baseUrl = baseUrl;
        this.apiKey = apiKey;
    }

    async createOrder(cartItems) {
        try {
            const orderData = {
                items: cartItems
            };

            if (this.apiKey) {
                orderData.api_key = this.apiKey;
            }

            console.log('🚀 Sending order to B2B API:', orderData);

            const response = await fetch(`${this.baseUrl}/api/create-order`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            });

            const result = await response.json();
            console.log('📡 B2B API Response:', result);

            if (!response.ok) {
                throw new Error(result.message || 'API-Fehler');
            }

            return result;

        } catch (error) {
            console.error('❌ Fehler beim Erstellen der Bestellung:', error);
            throw error;
        }
    }
}

// B2B API Client initialisieren
const b2bApi = new B2BApiClient(B2B_API_CONFIG.baseUrl, B2B_API_CONFIG.apiKey);

// ENHANCED B2C Funktionen mit Stock-Checking (but more permissive)

async function addToCart(name, price, quantityId) {
    console.log('🛒 B2C addToCart called with:', { name, price, quantityId });
    
    let quantity = document.getElementById(quantityId).value;
    console.log('📦 Quantity from input:', quantity);
    
    // Normalize product name for mapping
    const normalizedName = name.toLowerCase().trim();
    console.log(`🔍 Normalized name: "${normalizedName}"`);
    
    const productId = PRODUCT_MAPPING[normalizedName];
    console.log(`🆔 Product ID from mapping: ${productId}`);
    
    // Check stock first if we have a product mapping
    if (productId) {
        try {
            const stockInfo = await checkProductStock(productId);
            
            if (stockInfo.success && !stockInfo.error) {
                // Get current cart to check existing quantity
                let cart = JSON.parse(localStorage.getItem('b2c-cart')) || [];
                const existingItem = cart.find(item => item.name.toLowerCase().trim() === normalizedName);
                const currentCartQuantity = existingItem ? existingItem.quantity : 0;
                const totalRequestedQuantity = currentCartQuantity + parseInt(quantity);
                
                // Prevent adding if not enough stock
                if (totalRequestedQuantity > stockInfo.current_stock) {
                    const availableToAdd = Math.max(0, stockInfo.current_stock - currentCartQuantity);
                    
                    if (availableToAdd === 0) {
                        showStockMessage(name, `❌ Nicht verfügbar (Bestand: ${stockInfo.current_stock})`, true);
                        closePopup(`popup-${quantityId.split('-')[1]}`);
                        return; // BLOCK ADDING TO CART
                    } else {
                        showStockMessage(name, `❌ Nur noch ${availableToAdd} Stück verfügbar. Bitte Menge anpassen.`, true);
                        closePopup(`popup-${quantityId.split('-')[1]}`);
                        return; // BLOCK ADDING TO CART
                    }
                } else {
                    // Show success message with remaining stock
                    const remainingAfterAdd = stockInfo.current_stock - totalRequestedQuantity;
                    showStockMessage(name, `✅ ${quantity} Stück hinzugefügt (${remainingAfterAdd} noch verfügbar)`, false);
                }
            } else {
                // Stock check failed - show warning but allow adding
                showStockMessage(name, `⚠️ ${quantity} Stück hinzugefügt (Lagerbestand wird beim Checkout geprüft)`, true);
            }
        } catch (error) {
            console.error('❌ Stock check error, but proceeding with add:', error);
            showStockMessage(name, `⚠️ ${quantity} Stück hinzugefügt (Lagerbestand wird beim Checkout geprüft)`, true);
        }
    } else {
        console.warn(`⚠️ No product mapping found for "${name}"`);
        showStockMessage(name, `⚠️ ${quantity} Stück hinzugefügt (Lagerbestand wird beim Checkout geprüft)`, true);
    }
    
    // Add to cart (only reached if stock check passes or fails gracefully)
    let cartItem = {
        name: name,
        price: price,
        quantity: parseInt(quantity)
    };
    console.log('🎯 Created cart item:', cartItem);

    let cart = JSON.parse(localStorage.getItem('b2c-cart')) || [];
    console.log('📋 Current cart before adding:', cart);
    
    // Check if item already exists in cart
    const existingItemIndex = cart.findIndex(item => item.name.toLowerCase().trim() === name.toLowerCase().trim());
    
    if (existingItemIndex !== -1) {
        // Update existing item quantity
        cart[existingItemIndex].quantity += parseInt(quantity);
        console.log('📝 Updated existing item quantity');
    } else {
        // Add new item
        cart.push(cartItem);
        console.log('➕ Added new item to cart');
    }
    
    localStorage.setItem('b2c-cart', JSON.stringify(cart));
    console.log('✅ Cart after adding:', cart);

    alert('Produkt wurde zum Warenkorb hinzugefügt!');
    closePopup(`popup-${quantityId.split('-')[1]}`);
}

function displayCart() {
    let cartItemsDiv = document.getElementById('cart-items');
    if (!cartItemsDiv) {
        console.log('🔍 cart-items element not found');
        return;
    }

    let cart = JSON.parse(localStorage.getItem('b2c-cart')) || [];
    let totalPrice = 0;
    cartItemsDiv.innerHTML = '';

    console.log('🛒 Displaying cart with', cart.length, 'items');

    if (cart.length === 0) {
        cartItemsDiv.innerHTML = '<p>Dein Warenkorb ist Leer.</p>';
        if (document.getElementById('total-price')) {
            document.getElementById('total-price').innerText = '0.00';
        }
        return;
    }

    cart.forEach((item, index) => {
        let itemDiv = document.createElement('div');
        itemDiv.classList.add('cart-item');
        itemDiv.innerHTML = `
            <p><strong>${item.name}</strong></p>
            <p>Preis: ${item.price} €</p>
            <p>Anzahl: ${item.quantity}</p>
            <button onclick="removeFromCart(${index})">Entfernen</button>
            <hr>
        `;
        cartItemsDiv.appendChild(itemDiv);
        totalPrice += item.price * item.quantity;
    });
    
    if (document.getElementById('total-price')) {
        document.getElementById('total-price').innerText = totalPrice.toFixed(2);
    }
    
    console.log('💰 Total price calculated:', totalPrice.toFixed(2));
}

window.onload = displayCart;

function removeFromCart(index) {
    console.log('🗑️ Removing item at index:', index);
    let cart = JSON.parse(localStorage.getItem('b2c-cart')) || [];
    cart.splice(index, 1);
    localStorage.setItem('b2c-cart', JSON.stringify(cart));
    displayCart();
}

if (!localStorage.getItem('b2c-services-purchaseCounter')) {
    localStorage.setItem('b2c-services-purchaseCounter', 0);
}

// B2C-Format zu B2B-Format konvertieren
function convertB2CCartToB2BFormat(b2cCart) {
    return b2cCart.map((item, index) => {
        // Produktname normalisieren (klein, ohne extra Leerzeichen)
        const normalizedName = item.name.toLowerCase().trim();
        
        console.log(`🔍 Looking for mapping for: "${normalizedName}"`);
        
        // Produkt-ID aus Mapping holen
        let productId = PRODUCT_MAPPING[normalizedName];
        
        if (!productId) {
            console.warn(`⚠️ Warnung: Keine Produkt-ID gefunden für "${item.name}". Verwende Fallback-ID.`);
            // Fallback: verwende index + 1000 um Konflikte zu vermeiden
            productId = 1000 + index;
        }

        console.log(`🔄 Mapping: "${item.name}" -> Produkt-ID ${productId}`);

        return {
            id: productId,
            name: item.name,
            price: parseFloat(item.price),
            qty: parseInt(item.quantity)
        };
    });
}

// ENHANCED Bestellung-Abschließen Funktion mit sanfter Stock-Prüfung
async function bestellungAbschliessen() {
    console.log('🚀 Starting B2C checkout process with B2B integration...');
    
    let cart = JSON.parse(localStorage.getItem('b2c-cart')) || [];
    if (cart.length === 0) {
        alert('Dein Warenkorb ist Leer.');
        return;
    }

    let paymentMethod = document.getElementById('payment-method') ? document.getElementById('payment-method').value : 'Nicht angegeben';
    
    // Button deaktivieren während der Bestellung
    const confirmBtn = document.querySelector('.confirm-btn');
    const originalText = confirmBtn ? confirmBtn.textContent : '';
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Bestellung wird verarbeitet...';
    }

    try {
        // Convert to B2B format and send directly
        // The B2B API will handle stock validation
        console.log('🔄 Converting cart and sending to B2B API...');

        // B2C-Warenkorb ins B2B-Format konvertieren
        const b2bCartItems = convertB2CCartToB2BFormat(cart);
        console.log('🔄 Converted cart to B2B format:', b2bCartItems);

        // Bestellung an B2B-API senden (API will check stock)
        const result = await b2bApi.createOrder(b2bCartItems);
        
        if (result.success) {
            console.log('✅ Order successfully created in B2B system:', result);
            
            // Erfolgreiche Bestellung - lokale B2C-Verarbeitung
            let purchaseCounter = parseInt(localStorage.getItem('b2c-services-purchaseCounter')) + 1;
            localStorage.setItem('b2c-services-purchaseCounter', purchaseCounter);
            
            let totalPrice = result.total_price || cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            let orderDetails = ``;

            cart.forEach(item => {
                orderDetails += `<li style="margin: 8px 0; padding: 8px; background: white; border-radius: 4px;">
                    <strong>${item.name}</strong> - ${item.quantity} x ${item.price} € = ${(item.quantity * item.price).toFixed(2)} €
                </li>`;
            });

            orderDetails += `
                </ul>
                <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 20px 0;">
                    <p><strong>💰 Gesamtpreis:</strong> ${totalPrice.toFixed(2)} €</p>
                    <p><strong>📊 Gesamtmenge:</strong> ${result.total_quantity} Artikel</p>
                    <p><strong>💳 Zahlungsart:</strong> ${paymentMethod}</p>
                    <p><strong>📅 Bestelldatum:</strong> ${new Date().toLocaleString()}</p>
                </div>
                <h3 style="color: #28a745; text-align: center; margin-top: 30px;">
                    🙏 Vielen Dank für Ihren Einkauf bei Rolling-Willy!
                </h3>
            `;

            // Kassenbeleg in neuem Fenster anzeigen
            let newWindow = window.open('', '_blank', 'width=800,height=900,scrollbars=yes');
            newWindow.document.write(`
                <html>
                <head>
                    <title>Bestellbestätigung Rolling-Willy - ${result.verkauf_nr}</title>
                    <link href="https://fonts.googleapis.com/css2?family=Space+Mono&display=swap" rel="stylesheet">
                    <style>
                        body {
                            font-family: 'Space Mono', monospace;
                            padding: 30px;
                            background-color: #f8f9fa;
                            color: #333;
                            line-height: 1.6;
                            max-width: 800px;
                            margin: 0 auto;
                        }
                        h2, h3 {
                            color: #28a745;
                            border-bottom: 2px solid #28a745;
                            padding-bottom: 10px;
                        }
                        h4 {
                            color: #6f42c1;
                            margin-top: 20px;
                        }
                        ul {
                            list-style: none;
                            padding: 0;
                        }
                        li {
                            background: #fff;
                            padding: 12px;
                            margin: 8px 0;
                            border-radius: 6px;
                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                            border-left: 4px solid #007bff;
                        }
                        p {
                            margin: 12px 0;
                        }
                        .success-badge {
                            background: #28a745;
                            color: white;
                            padding: 8px 15px;
                            border-radius: 20px;
                            font-size: 14px;
                            display: inline-block;
                            margin: 20px 0;
                        }
                        .header {
                            text-align: center;
                            border-bottom: 3px solid #007bff;
                            padding-bottom: 20px;
                            margin-bottom: 30px;
                        }
                        .logo {
                            font-size: 24px;
                            font-weight: bold;
                            color: #007bff;
                        }
                        @media print {
                            body { background: white; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="logo">🛴 Rolling-Willy</div>
                        <p>Ihr E-Roller Spezialist</p>
                    </div>
                    ${orderDetails}
                    <div class="success-badge">
                        ✅ Bestellung erfolgreich verarbeitet
                    </div>
                    <div class="no-print" style="text-align: center; margin-top: 30px;">
                        <button onclick="window.print()" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                            🖨️ Beleg drucken
                        </button>
                        <button onclick="window.close()" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                            ❌ Schließen
                        </button>
                    </div>
                </body>
                </html>
            `);

            // B2C Warenkorb leeren und stock cache leeren
            localStorage.removeItem('b2c-cart');
            stockCache = {};
            stockCacheExpiry = {};
            displayCart();
            
            // Success message
            alert(`🎉 Bestellung erfolgreich aufgegeben!\n\n✅ B2B Verkauf-Nr: ${result.verkauf_nr}\n💰 Gesamtpreis: ${totalPrice.toFixed(2)} €\n📦 Artikel: ${result.total_quantity}\n\nIhr Kassenbeleg wird in einem neuen Fenster geöffnet.`);

        } else {
            throw new Error('Unerwartete Antwort vom B2B-System');
        }

    } catch (error) {
        console.error('❌ Fehler bei der B2B-Bestellung:', error);
        
        // Check if it's a stock error from the API
        if (error.message && error.message.includes('Nicht genügend Bestand')) {
            alert(`⚠️ ${error.message}\n\nBitte passen Sie die Mengen in Ihrem Warenkorb an.`);
        } else {
            // Fallback: Lokale B2C-Bestellung ohne B2B-Integration
            console.log('💾 Fallback: Creating local B2C order...');
            
            let purchaseCounter = parseInt(localStorage.getItem('b2c-services-purchaseCounter')) + 1;
            localStorage.setItem('b2c-services-purchaseCounter', purchaseCounter);
            
            let totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            // Fallback handling...
            localStorage.removeItem('b2c-cart');
            displayCart();
            
            alert(`⚠️ Bestellung wurde lokal verarbeitet!\n\nFehler: ${error.message}\n\n📞 Bitte kontaktieren Sie uns für die finale Bearbeitung:\ninfo@rolling-willy.de\n\nLokale Bestellnummer: ${purchaseCounter}`);
        }
        
    } finally {
        // Button wieder aktivieren
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        }
    }
}

// DEBUG: System-Info in Konsole ausgeben
console.log('🛒 B2C Cart System loaded with B2B integration and Stock Checking');
console.log('🔧 B2B API URL:', B2B_API_CONFIG.baseUrl);
console.log('🔑 API Key configured:', !!B2B_API_CONFIG.apiKey);
console.log('📋 Product mapping loaded:', Object.keys(PRODUCT_MAPPING).length, 'products');
console.log('🗺️ Product mappings:', PRODUCT_MAPPING);
console.log('📦 Current B2C cart:', localStorage.getItem('b2c-cart'));