<?php
// templates/sales_content.php
?>
<script src="<?php echo BASE_URL; ?>/assets/js/sales.js" defer></script>
<main class="main-content flex-grow p-4 md:p-6 bg-white overflow-y-auto flex flex-col order-2 lg:order-none h-auto">
    <header class="flex flex-col sm:flex-row justify-between items-center mb-6 pb-3 border-b border-slate-200 gap-4">
        <h1 class="text-2xl font-semibold text-slate-700">Sales Overview</h1>
        <div class="flex flex-col sm:flex-row gap-2 items-center">
            <div class="flex gap-2 items-center">
                <label for="reportStartDate" class="text-sm text-slate-600">From:</label>
                <input type="date" id="reportStartDate" name="report_start_date"
                    class="p-1.5 border border-slate-300 rounded-md text-sm">
                <label for="reportEndDate" class="text-sm text-slate-600">To:</label>
                <input type="date" id="reportEndDate" name="report_end_date"
                    class="p-1.5 border border-slate-300 rounded-md text-sm">
            </div>
            <button id="printSalesReportBtn"
                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-3 rounded text-sm shadow">
                Print Sales Report
            </button>
        </div>
    </header>

    <section id="salesSummaryContainer" class="space-y-6">
        <div id="salesLoading" class="text-center py-10">
            <p class="text-slate-500 text-lg">Loading sales data...</p>
            <svg class="animate-spin h-8 w-8 text-slate-500 mx-auto mt-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>

        <div id="salesDataWrapper" class="hidden">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div class="bg-slate-50 p-4 rounded-lg shadow border border-slate-200">
                    <h3 class="text-sm font-medium text-slate-500">Total Revenue</h3>
                    <p id="totalSalesAmount" class="text-2xl font-semibold text-green-600">P 0.00</p>
                </div>
                <div class="bg-slate-50 p-4 rounded-lg shadow border border-slate-200">
                    <h3 class="text-sm font-medium text-slate-500">Total Orders (Completed)</h3>
                    <p id="totalOrdersCount" class="text-2xl font-semibold text-blue-600">0</p>
                </div>
                <div class="bg-slate-50 p-4 rounded-lg shadow border border-slate-200">
                    <h3 class="text-sm font-medium text-slate-500">Average Order Value</h3>
                    <p id="averageOrderValue" class="text-2xl font-semibold text-amber-600">P 0.00</p>
                </div>
            </div>

            <div class="bg-slate-50 p-4 rounded-lg shadow border border-slate-200 mb-6">
                <h3 class="text-lg font-semibold text-slate-700 mb-3">Recent Completed Orders</h3>
                <div id="recentOrdersList" class="text-sm text-slate-600 space-y-2">
                    <p class="italic no-recent-orders">No recent completed orders found.</p>
                </div>
            </div>

            <div class="bg-slate-50 p-4 rounded-lg shadow border border-slate-200">
                <h3 class="text-lg font-semibold text-slate-700 mb-3">Top Selling Products</h3>
                <div id="topSellingProductsList" class="text-sm text-slate-600 space-y-2">
                    <p class="italic">Top selling product data not yet available.</p>
                </div>
            </div>
        </div>
        <div id="noSalesDataMessage" class="hidden text-center py-10">
            <p class="text-slate-500 text-lg">No sales data available yet.</p>
        </div>
    </section>
</main>