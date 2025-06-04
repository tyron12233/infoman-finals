<script>
    // JavaScript will be placed here
</script>

<style>
    @media (max-width: 1023px) {
        .body-no-scroll-mobile {
            overflow: hidden;
        }
    }
</style>

<button id="mobileSummaryToggle"
    class="fixed bottom-0 left-0 right-0 bg-amber-500 text-white p-3 text-center z-50 lg:hidden">
    View Order (0 items - P0.00)
</button>

<div id="bottomSheetOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

<aside id="orderSummary" class="order-summary w-full bg-slate-50 p-4 md:p-6 border-t lg:border-t-0 lg:border-l border-slate-200 flex flex-col flex-shrink-0 overflow-y-auto
           fixed bottom-0 left-0 right-0 h-[90vh] transform translate-y-full transition-transform duration-300 ease-in-out z-40
           lg:w-96 lg:static lg:h-auto lg:max-h-full lg:translate-y-0 lg:order-none lg:flex">
    <button id="closeSummarySheet" class="absolute top-4 right-4 text-slate-500 hover:text-slate-700 lg:hidden z-10">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <h3 class="text-xl font-semibold mb-3 text-slate-700 border-b border-slate-200 pb-3 pr-8">Order #<span
            id="orderNumber">001</span></h3>
    <div class="order-meta mb-4">
        <label for="customerName" class="block text-sm text-slate-600 mb-1">Customer Name</label>
        <input type="text" id="customerName" placeholder="Enter customer name (optional)"
            class="w-full p-2 border border-slate-300 rounded-md mb-3 text-sm focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none">
        <div class="order-type-selector flex gap-2 mb-3">
            <button
                class="order-type-btn flex-grow py-2 px-3 border border-slate-300 bg-white text-slate-600 cursor-pointer rounded-md text-center text-sm transition duration-200 hover:bg-slate-100 selected"
                data-type="Dine In">Dine In</button>
            <button
                class="order-type-btn flex-grow py-2 px-3 border border-slate-300 bg-white text-slate-600 cursor-pointer rounded-md text-center text-sm transition duration-200 hover:bg-slate-100"
                data-type="Take Out">Take Out</button>
        </div>
    </div>

    <h4 class="text-lg font-medium text-slate-700 mb-2">Items</h4>
    <div class="order-items-list mb-4" id="orderItemsList">
        <p class="empty-cart-message text-center text-slate-500 p-5 italic">Your cart is empty.</p>
    </div>

    <div class="flex-grow">

    </div>

    <div class="totals pt-4 border-t border-slate-300">
        <div class="flex justify-between mb-2 text-sm">
            <span class="text-slate-600">No. of Items</span>
            <span id="totalItemsCount" class="text-slate-700 font-medium">0</span>
        </div>
        <div class="flex justify-between mb-2 text-sm">
            <span class="text-slate-600">Sub Total</span>
            <span class="text-slate-700 font-medium">P<span id="subTotalPrice">0.00</span></span>
        </div>
        <div class="flex justify-between mb-2 text-lg font-bold">
            <span class="text-amber-600">TOTAL</span>
            <span class="text-amber-600">P<span id="finalTotalPrice">0.00</span></span>
        </div>
    </div>
    <button
        class="place-order-btn w-full py-3 bg-green-500 text-white border-none rounded-lg cursor-pointer text-base font-bold mt-4 transition duration-300 hover:bg-green-600 disabled:bg-slate-300 disabled:cursor-not-allowed"
        id="placeOrderBtn" disabled>Place Order</button>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const orderSummary = document.getElementById('orderSummary');
        const mobileSummaryToggle = document.getElementById('mobileSummaryToggle');
        const closeSummarySheet = document.getElementById('closeSummarySheet');
        const bottomSheetOverlay = document.getElementById('bottomSheetOverlay');

        const totalItemsCountSpan = document.getElementById('totalItemsCount');
        const finalTotalPriceSpan = document.getElementById('finalTotalPrice');
        // const customerNameInput = document.getElementById('customerName'); // Example, if needed
        // const placeOrderBtn = document.getElementById('placeOrderBtn'); // Example, if needed

        // Function to check if on mobile (screen width < 1024px, Tailwind's lg breakpoint)
        function isMobile() {
            return window.innerWidth < 1024;
        }

        // Function to update the mobile toggle bar text
        function updateMobileToggleText() {
            if (!mobileSummaryToggle || !totalItemsCountSpan || !finalTotalPriceSpan) return;

            const totalItems = totalItemsCountSpan.textContent;
            const totalPrice = finalTotalPriceSpan.textContent;
            mobileSummaryToggle.textContent = `View Order (${totalItems} items - P${totalPrice})`;
        }

        // CSS class for preventing body scroll on mobile when sheet is open
        const bodyNoScrollClass = 'body-no-scroll-mobile';

        // Add this CSS rule to your global stylesheet or a <style> tag in <head>

        // For this example, I'll create and inject the style rule dynamically if it doesn't exist.
        if (!document.querySelector('style[data-dynamic-scroll-lock]')) {
            const styleSheet = document.createElement("style");
            styleSheet.setAttribute("data-dynamic-scroll-lock", "true");
            styleSheet.innerHTML = `@media (max-width: 1023px) { .${bodyNoScrollClass} { overflow: hidden; } }`;
            document.head.appendChild(styleSheet);
        }


        function openSheet() {
            if (!orderSummary || !isMobile()) return;

            orderSummary.classList.remove('translate-y-full');
            orderSummary.classList.add('translate-y-[0vh]'); // Sheet slides up to occupy bottom 90vh
            if (mobileSummaryToggle) mobileSummaryToggle.classList.add('hidden');
            if (bottomSheetOverlay) bottomSheetOverlay.classList.remove('hidden');
            document.body.classList.add(bodyNoScrollClass);
        }

        function closeSheet() {
            if (!orderSummary || !isMobile()) return;

            orderSummary.classList.remove('translate-y-[0vh]');
            orderSummary.classList.add('translate-y-full'); // Slides down
            if (mobileSummaryToggle) mobileSummaryToggle.classList.remove('hidden');
            if (bottomSheetOverlay) bottomSheetOverlay.classList.add('hidden');
            document.body.classList.remove(bodyNoScrollClass);
        }

        // Function to handle behavior on window resize
        function handleSheetStateOnResize() {
            if (isMobile()) {
                closeSheet();
                // If sheet is meant to be open (not translate-y-full), ensure toggle is hidden and body scroll lock might be active
                if (!orderSummary.classList.contains('translate-y-full')) {
                    if (mobileSummaryToggle) mobileSummaryToggle.classList.add('hidden');
                    // if it was open, ensure body scroll class is present
                    document.body.classList.add(bodyNoScrollClass);
                } else { // Sheet is closed or should be closed
                    if (mobileSummaryToggle) mobileSummaryToggle.classList.remove('hidden');
                    document.body.classList.remove(bodyNoScrollClass); // ensure no scroll lock
                }
                updateMobileToggleText(); // Update text on resize for mobile
            } else { // Desktop view
                if (mobileSummaryToggle) mobileSummaryToggle.classList.add('hidden'); // Hide mobile toggle
                if (bottomSheetOverlay) bottomSheetOverlay.classList.add('hidden'); // Hide overlay
                // Ensure order summary is not translated (Tailwind's lg:translate-y-0 handles this)
                // Remove any mobile-specific transform states and body scroll locks.
                orderSummary.classList.remove('translate-y-full', 'translate-y-[10vh]');
                document.body.classList.remove(bodyNoScrollClass);
            }
        }

        // Event Listeners
        if (mobileSummaryToggle) {
            mobileSummaryToggle.addEventListener('click', () => {
                if (isMobile()) openSheet();
            });
        }

        if (closeSummarySheet) {
            closeSummarySheet.addEventListener('click', () => {
                if (isMobile()) closeSheet();
            });
        }

        if (bottomSheetOverlay) {
            bottomSheetOverlay.addEventListener('click', () => {
                if (isMobile() && !orderSummary.classList.contains('translate-y-full')) {
                    closeSheet();
                }
            });
        }

        // MutationObserver to update toggle bar text when order total/items change
        const observerCallback = () => {
            if (isMobile()) {
                updateMobileToggleText();
            }
        };
        const observer = new MutationObserver(observerCallback);
        if (totalItemsCountSpan && finalTotalPriceSpan) {
            observer.observe(totalItemsCountSpan, { childList: true, characterData: true, subtree: true });
            observer.observe(finalTotalPriceSpan, { childList: true, characterData: true, subtree: true });
        }

        // Initial setup on page load
        handleSheetStateOnResize();
        // Add resize event listener
        window.addEventListener('resize', handleSheetStateOnResize);

        // --- Example function to simulate adding items to the order ---
        // You would replace this with your actual cart update logic.
        /*
        function addExampleItemToCart(name, price, quantity) {
            const currentTotalItems = parseInt(totalItemsCountSpan.textContent || '0');
            const currentFinalTotal = parseFloat(finalTotalPriceSpan.textContent || '0.00');
            const currentSubTotal = parseFloat(document.getElementById('subTotalPrice').textContent || '0.00');
    
    
            const itemTotalPrice = price * quantity;
    
            totalItemsCountSpan.textContent = currentTotalItems + quantity;
            const newSubTotal = currentSubTotal + itemTotalPrice;
            const newFinalTotal = currentFinalTotal + itemTotalPrice;
    
            document.getElementById('subTotalPrice').textContent = newSubTotal.toFixed(2);
            finalTotalPriceSpan.textContent = newFinalTotal.toFixed(2);
            
            // Enable place order button if it was disabled
            const placeOrderButton = document.getElementById('placeOrderBtn');
            if (placeOrderButton) placeOrderButton.disabled = false;
    
            // Remove empty cart message
            const emptyCartMsg = document.querySelector('.empty-cart-message');
            if (emptyCartMsg) emptyCartMsg.style.display = 'none';
    
            // Add item to UI (very basic example)
            const orderItemsList = document.getElementById('orderItemsList');
            const itemElement = document.createElement('div');
            itemElement.className = 'order-item flex justify-between items-center py-2 border-b border-slate-100 text-sm';
            itemElement.innerHTML = `
                <div>
                    <p class="font-medium text-slate-700">${name}</p>
                    <p class="text-xs text-slate-500">P${price.toFixed(2)} x ${quantity}</p>
                </div>
                <p class="font-medium text-slate-700">P${itemTotalPrice.toFixed(2)}</p>
            `;
            if(orderItemsList) orderItemsList.appendChild(itemElement);
    
            // The MutationObserver will automatically call updateMobileToggleText()
        }
    
        // Example usage:
        setTimeout(() => addExampleItemToCart("Sample Item 1", 150.00, 1), 1000);
        setTimeout(() => addExampleItemToCart("Sample Item 2", 75.00, 2), 2500);
        */
    });
</script>