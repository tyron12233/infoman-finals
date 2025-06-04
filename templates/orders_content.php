<?php
/**
 * This template will be populated by JavaScript.
 * It provides the basic structure for the orders list.
 */
?>
<!-- order.js -->
<script src="../assets/js/orders.js" defer></script>

<main class="main-content flex-grow p-4 md:p-6 bg-white overflow-y-auto flex flex-col order-2 lg:order-none h-auto">
    <header class="flex justify-between items-center mb-6 pb-3 border-b border-slate-200">
        <h1 class="text-2xl font-semibold text-slate-700">View Orders</h1>
        <!-- Add any filters or actions here if needed, e.g., date range picker -->
    </header>

    <section id="ordersContainer" class="space-y-6">
        <!-- Orders will be loaded here by JavaScript -->
        <div id="ordersLoading" class="text-center py-10">
            <p class="text-slate-500 text-lg">Loading orders...</p>
            <!-- You can add a spinner icon here -->
            <svg class="animate-spin h-8 w-8 text-slate-500 mx-auto mt-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>
        <div id="noOrdersMessage" class="hidden text-center py-10">
            <p class="text-slate-500 text-lg">No orders found.</p>
        </div>
    </section>
</main>