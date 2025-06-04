<aside
    class="order-summary w-full lg:w-96 bg-slate-50 p-4 md:p-6 border-t lg:border-t-0 lg:border-l border-slate-200 flex flex-col flex-shrink-0 overflow-y-auto order-3 lg:order-none max-h-[50vh] lg:max-h-full">
    <h3 class="text-xl font-semibold mb-3 text-slate-700 border-b border-slate-200 pb-3">Order #<span
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
    <div class="order-items-list flex-grow mb-4" id="orderItemsList">
        <p class="empty-cart-message text-center text-slate-500 p-5 italic">Your cart is empty.</p>
    </div>

    <div class="totals mt-auto pt-4 border-t border-slate-300">
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