
document.addEventListener('DOMContentLoaded', function () {
    // --- Sidebar Active State Visuals ---
    // This script was originally inline in the sidebar
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        if (link.classList.contains('active')) {
            // Apply active styles (Tailwind classes)
            link.classList.add('lg:border-amber-500', 'bg-slate-700', 'lg:bg-white', 'lg:text-slate-800');
            link.classList.remove('text-slate-300', 'lg:border-transparent');
        }
        link.addEventListener('click', function (e) {
            // Comment out preventDefault if actual navigation is desired
            // e.preventDefault();
            document.querySelectorAll('.sidebar .nav-link').forEach(l => {
                l.classList.remove('active', 'lg:border-amber-500', 'bg-slate-700', 'lg:bg-white', 'lg:text-slate-800');
                l.classList.add('text-slate-300', 'lg:border-transparent');
            });
            this.classList.add('active', 'lg:border-amber-500', 'bg-slate-700', 'lg:bg-white', 'lg:text-slate-800');
            this.classList.remove('text-slate-300', 'lg:border-transparent');
        });
    });


    // --- Cart State and Elements ---
    let cart = [];
    const orderItemsList = document.getElementById('orderItemsList');
    const totalItemsCountEl = document.getElementById('totalItemsCount');
    const subTotalPriceEl = document.getElementById('subTotalPrice');
    const finalTotalPriceEl = document.getElementById('finalTotalPrice');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const emptyCartMessageHTML = `<p class="empty-cart-message text-center text-slate-500 p-5 italic">Your cart is empty.</p>`;
    let currentOrderType = 'Dine In'; // Default order type

    // --- Category Filtering ---
    // This relies on PHP to set the initial 'active' class and category ID in the URL
    document.querySelectorAll('.category-btn').forEach(button => {
        button.addEventListener('click', function () {
            const categoryId = this.dataset.categoryId;
            // The page will reload with the new category parameter.
            // JavaScript doesn't need to remove/add 'active' here as PHP handles it on page load.
            // However, for instant visual feedback before reload (optional):
            // document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active', 'bg-slate-700', 'text-white', 'border-slate-700'));
            // this.classList.add('active', 'bg-slate-700', 'text-white', 'border-slate-700');
            window.location.href = `index.php?category=${categoryId}`; // Assumes current page is index.php in dashboard
        });
    });
    // Ensure the PHP-set active button has the correct JS-driven styles if they differ
    const activeCatButton = document.querySelector('.category-btn.active');
    if (activeCatButton) {
        activeCatButton.classList.add('bg-slate-700', 'text-white', 'border-slate-700');
    }

    // --- Product Search (Basic Client-Side Filter) ---
    const productSearchInput = document.getElementById('productSearchInput');
    const productGrid = document.getElementById('productGrid');
    if (productSearchInput && productGrid) {
        const allProductCards = Array.from(productGrid.querySelectorAll('.product-card'));

        productSearchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase().trim();
            const currentSelectedCategoryButton = document.querySelector('.category-btn.active');
            const currentSelectedCategory = currentSelectedCategoryButton ? currentSelectedCategoryButton.dataset.categoryId : 'all';

            allProductCards.forEach(card => {
                const productName = card.dataset.productName ? card.dataset.productName.toLowerCase() : '';
                const categoryIdOfCard = card.dataset.categoryId;

                let categoryMatch = (currentSelectedCategory === 'all' || categoryIdOfCard === currentSelectedCategory);
                let nameMatch = productName.includes(searchTerm);

                if (categoryMatch && nameMatch) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });
    }


    // --- Variant Selection ---
    // Delegate event listener to productGrid for dynamically added products (if any in future)
    if (productGrid) {
        productGrid.addEventListener('click', function (e) {
            if (e.target.matches('.variant-btn')) {
                const button = e.target;
                const parentOptions = button.closest('.variant-options');
                if (parentOptions) {
                    parentOptions.querySelectorAll('.variant-btn').forEach(btn => {
                        btn.classList.remove('selected', 'bg-slate-700', 'text-white', 'border-slate-700');
                        btn.classList.add('bg-white', 'text-slate-600', 'border-slate-300');
                    });
                    button.classList.add('selected', 'bg-slate-700', 'text-white', 'border-slate-700');
                    button.classList.remove('bg-white', 'text-slate-600', 'border-slate-300');
                }
            }
        });
    }
    // Initial styling for pre-selected variants (if any)
    document.querySelectorAll('.variant-btn.selected').forEach(btn => {
        btn.classList.add('bg-slate-700', 'text-white', 'border-slate-700');
        btn.classList.remove('bg-white', 'text-slate-600', 'border-slate-300');
    });


    // --- Add to Cart ---
    // Delegate event listener to productGrid
    if (productGrid) {
        productGrid.addEventListener('click', function (e) {
            if (e.target.matches('.add-to-cart-btn')) {
                const button = e.target;
                const card = button.closest('.product-card');
                if (!card) return;

                const productId = card.dataset.productId;
                const productName = card.dataset.productName;
                const productImageUrl = card.querySelector('img') ? card.querySelector('img').src : 'https://via.placeholder.com/50';

                const selectedVariantButton = card.querySelector('.variant-btn.selected');
                if (!selectedVariantButton) {
                    showCustomDialog('Selection Required', 'Please select a product variant before adding to cart.', 'warning');
                    return;
                }

                const variantId = selectedVariantButton.dataset.variantId;
                const variantName = selectedVariantButton.dataset.variantName;
                const variantPrice = parseFloat(selectedVariantButton.dataset.variantPrice);
                const variantSku = selectedVariantButton.dataset.variantSku;

                const cartItemId = `variant-${variantId}`; // Unique ID for cart item based on variant
                const existingItem = cart.find(item => item.id === cartItemId);

                if (existingItem) {
                    existingItem.quantity++;
                } else {
                    cart.push({
                        id: cartItemId,
                        productId: productId,
                        productName: productName,
                        variantId: variantId,
                        variantName: variantName,
                        price: variantPrice,
                        sku: variantSku, // Store SKU
                        quantity: 1,
                        imageUrl: productImageUrl,
                        notes: ''
                    });
                }
                updateCartUI();
            }
        });
    }


    // --- Order Type Selection ---
    document.querySelectorAll('.order-type-btn').forEach(button => {
        button.addEventListener('click', function () {
            document.querySelectorAll('.order-type-btn').forEach(btn => {
                btn.classList.remove('selected', 'bg-slate-700', 'text-white', 'border-slate-700');
                btn.classList.add('bg-white', 'text-slate-600', 'border-slate-300');
            });
            this.classList.add('selected', 'bg-slate-700', 'text-white', 'border-slate-700');
            this.classList.remove('bg-white', 'text-slate-600', 'border-slate-300');
            currentOrderType = this.dataset.type;
        });
    });
    // Initial styling for pre-selected order type
    const activeOrderTypeButton = document.querySelector('.order-type-btn.selected');
    if (activeOrderTypeButton) {
        activeOrderTypeButton.classList.add('bg-slate-700', 'text-white', 'border-slate-700');
        activeOrderTypeButton.classList.remove('bg-white', 'text-slate-600', 'border-slate-300');
        currentOrderType = activeOrderTypeButton.dataset.type; // Ensure currentOrderType is set
    }


    // --- Cart UI Update Functions ---
    function updateCartUI() {
        if (!orderItemsList) return;

        if (cart.length === 0) {
            orderItemsList.innerHTML = emptyCartMessageHTML;
        } else {
            orderItemsList.innerHTML = ''; // Clear current items
            cart.forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.classList.add('order-item', 'flex', 'items-start', 'mb-4', 'pb-4', 'border-b', 'border-slate-200', 'last:border-b-0', 'last:mb-0');
                itemElement.dataset.itemId = item.id;
                itemElement.innerHTML = `
                    <img src="${item.imageUrl}" alt="${item.productName}" class="w-12 h-12 object-cover rounded-md mr-3 flex-shrink-0">
                    <div class="item-details flex-grow mr-2">
                        <div class="item-name font-semibold text-sm text-slate-700 line-clamp-1" title="${item.productName} (${item.variantName})">${item.productName} (${item.variantName})</div>
                        <div class="item-price text-xs text-slate-500">P ${item.price.toFixed(2)}</div>
                        <input type="text" class="item-notes-input w-full text-xs p-1 mt-1 border border-slate-300 rounded focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none" placeholder="Order Notes..." value="${item.notes || ''}" data-item-id="${item.id}">
                    </div>
                    <div class="quantity-controls flex items-center flex-shrink-0 self-center">
                        <button class="quantity-btn decrease-qty bg-slate-200 border border-slate-300 text-slate-600 w-7 h-7 cursor-pointer text-lg rounded flex items-center justify-center transition hover:bg-slate-300" data-item-id="${item.id}">-</button>
                        <span class="item-quantity w-8 text-center text-sm px-1">${item.quantity}</span>
                        <button class="quantity-btn increase-qty bg-slate-200 border border-slate-300 text-slate-600 w-7 h-7 cursor-pointer text-lg rounded flex items-center justify-center transition hover:bg-slate-300" data-item-id="${item.id}">+</button>
                    </div>
                    <button class="remove-item-btn bg-transparent border-none text-red-500 cursor-pointer text-xl p-1 hover:text-red-700 ml-2 flex-shrink-0 self-center" data-item-id="${item.id}" aria-label="Remove item">&#x1F5D1;</button>
                `;
                orderItemsList.appendChild(itemElement);
            });
        }
        updateTotals();
        // Event listeners for cart items are now delegated (see below)
    }

    // --- Delegated Event Listeners for Cart Items ---
    if (orderItemsList) {
        orderItemsList.addEventListener('click', function (e) {
            const target = e.target;
            const itemId = target.dataset.itemId;

            if (target.matches('.decrease-qty')) {
                updateQuantity(itemId, -1);
            } else if (target.matches('.increase-qty')) {
                updateQuantity(itemId, 1);
            } else if (target.matches('.remove-item-btn')) {
                removeItem(itemId);
            }
        });

        orderItemsList.addEventListener('change', function (e) {
            const target = e.target;
            if (target.matches('.item-notes-input')) {
                const itemId = target.dataset.itemId; // Ensure notes input has data-item-id
                const cartItem = cart.find(ci => ci.id === itemId);
                if (cartItem) {
                    cartItem.notes = target.value;
                    console.log("Note updated for", itemId, ":", cartItem.notes); // For debugging
                }
            }
        });
    }


    function updateQuantity(itemId, change) {
        const itemIndex = cart.findIndex(item => item.id === itemId);
        if (itemIndex > -1) {
            cart[itemIndex].quantity += change;
            if (cart[itemIndex].quantity <= 0) {
                cart.splice(itemIndex, 1); // Remove item if quantity is 0 or less
            }
        }
        updateCartUI();
    }

    function removeItem(itemId) {
        cart = cart.filter(item => item.id !== itemId);
        updateCartUI();
    }

    function updateTotals() {
        if (!totalItemsCountEl || !subTotalPriceEl || !finalTotalPriceEl || !placeOrderBtn) return;

        let itemsCount = 0;
        let subTotal = 0;

        cart.forEach(item => {
            itemsCount += item.quantity;
            subTotal += item.price * item.quantity;
        });

        totalItemsCountEl.textContent = itemsCount;
        subTotalPriceEl.textContent = subTotal.toFixed(2);
        finalTotalPriceEl.textContent = subTotal.toFixed(2); // Assuming no tax/discount for now

        placeOrderBtn.disabled = cart.length === 0;
    }

    // --- Place Order ---
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', function () {
            if (cart.length === 0) {
                showCustomDialog('Empty Cart', "Your cart is empty. Please add some items before placing an order.", 'warning');
                return;
            }

            const customerNameInput = document.getElementById('customerName');
            const customerName = customerNameInput ? customerNameInput.value.trim() : '';
            const orderNumberEl = document.getElementById('orderNumber');
            const currentOrderNumber = orderNumberEl ? orderNumberEl.textContent : 'N/A';


            const orderData = {
                orderNumber: currentOrderNumber,
                customerName: customerName,
                orderType: currentOrderType,
                items: cart.map(item => ({
                    product_id: item.productId, // Original product ID
                    variant_id: item.variantId,
                    sku_at_purchase: item.sku,
                    product_name_at_purchase: `${item.productName} (${item.variantName})`, // For record keeping
                    quantity: item.quantity,
                    price_at_purchase: item.price,
                    notes: item.notes || ''
                })),
                subTotalAmount: parseFloat(subTotalPriceEl.textContent),
                totalAmount: parseFloat(finalTotalPriceEl.textContent)
                // You might add tax, discount, grandTotal later
            };

            console.log("Placing Order (Simulation):", JSON.stringify(orderData, null, 2));
            // showCustomDialog('Order Simulation', `Order would be placed for P ${orderData.totalAmount.toFixed(2)}. Check console for details.`, 'info');

            // --- Actual Fetch to Backend ---
            // Make sure 'place_order.php' is in the correct path relative to your public root
            // If dashboard is at public/dashboard/index.php, then place_order.php
            // at project_root/place_order.php would be accessed via '../place_order.php'
            // If place_order.php is at public/api/place_order.php, then '../api/place_order.php'
            // For simplicity, let's assume it's in the project root for now.
            // A better place would be an 'api' folder inside 'public'.
            // Example: fetch('../api/place_order.php', { ... })
            fetch('../api/place_order.php', { // Adjust path as needed
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(orderData)
            })
                .then(response => {
                    if (!response.ok) {
                        // Try to get error message from response if possible
                        return response.json().then(errData => {
                            throw new Error(errData.message || `HTTP error! Status: ${response.status}`);
                        }).catch(() => {
                            // If response is not JSON or no message field
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showCustomDialog('Order Placed!', `Order #${data.orderId} has been placed successfully.`, 'success');
                        cart = []; // Clear the cart
                        updateCartUI(); // Update UI
                        if (customerNameInput) customerNameInput.value = ''; // Clear customer name
                        if (orderNumberEl && data.newOrderNumber) { // Update order number on UI if backend provides it
                            orderNumberEl.textContent = data.newOrderNumber;
                        }
                    } else {
                        showCustomDialog('Order Failed', `Failed to place order: ${data.message || 'Unknown error from server.'}`, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error placing order:', error);
                    showCustomDialog('Order Error', `An error occurred: ${error.message}. Please check console and try again.`, 'error');
                });
        });
    }

    // Initial UI setup
    updateCartUI(); // Call to display empty cart message or existing items if any (e.g., from saved state not implemented here)
});
