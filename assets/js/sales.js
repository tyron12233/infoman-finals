// assets/js/sales.js

document.addEventListener('DOMContentLoaded', function () {
    const salesLoadingDiv = document.getElementById('salesLoading');
    const salesDataWrapper = document.getElementById('salesDataWrapper');
    const noSalesDataMessageDiv = document.getElementById('noSalesDataMessage');

    const totalSalesAmountEl = document.getElementById('totalSalesAmount');
    const totalOrdersCountEl = document.getElementById('totalOrdersCount');
    const averageOrderValueEl = document.getElementById('averageOrderValue');
    const recentOrdersListEl = document.getElementById('recentOrdersList');
    const topSellingProductsListEl = document.getElementById('topSellingProductsList');

    const printSalesReportBtn = document.getElementById('printSalesReportBtn');
    const reportStartDateInput = document.getElementById('reportStartDate');
    const reportEndDateInput = document.getElementById('reportEndDate');

    if (!salesLoadingDiv || !salesDataWrapper || !totalSalesAmountEl || !totalOrdersCountEl || !averageOrderValueEl || !recentOrdersListEl || !noSalesDataMessageDiv || !printSalesReportBtn || !reportStartDateInput || !reportEndDateInput) {
        console.error('One or more required DOM elements for sales page not found.');
        return;
    }

    function formatPrice(amount, currency = 'P') {
        return `${currency} ${parseFloat(amount).toFixed(2)}`;
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        } catch (e) {
            return dateString;
        }
    }

    function displaySalesSummary(summary) {
        salesLoadingDiv.classList.add('hidden');

        if (!summary || (summary.total_orders_count === 0 && summary.total_sales_amount === 0)) {
            noSalesDataMessageDiv.classList.remove('hidden');
            salesDataWrapper.classList.add('hidden');
            return;
        }

        noSalesDataMessageDiv.classList.add('hidden');
        salesDataWrapper.classList.remove('hidden');

        totalSalesAmountEl.textContent = formatPrice(summary.total_sales_amount || 0);
        totalOrdersCountEl.textContent = summary.total_orders_count || 0;
        averageOrderValueEl.textContent = formatPrice(summary.average_order_value || 0);

        // Display recent orders
        if (summary.recent_completed_orders && summary.recent_completed_orders.length > 0) {
            recentOrdersListEl.innerHTML = ''; // Clear placeholder
            summary.recent_completed_orders.forEach(order => {
                const orderDiv = document.createElement('div');
                orderDiv.className = 'pb-2 mb-2 border-b border-slate-200 last:border-b-0 last:mb-0 last:pb-0';
                orderDiv.innerHTML = `
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-slate-700">#${order.order_number} (${order.customer_name || 'N/A'})</span>
                        <span class="text-green-600 font-semibold">${formatPrice(order.total_amount)}</span>
                    </div>
                    <span class="text-xs text-slate-500">${formatDate(order.created_at)}</span>
                `;
                recentOrdersListEl.appendChild(orderDiv);
            });
        } else {
            recentOrdersListEl.querySelector('.no-recent-orders')?.classList.remove('hidden');
        }

        console.log('Top Selling Products:', summary.top_selling_products);
        if (topSellingProductsListEl && summary.top_selling_products && summary.top_selling_products.length > 0) {
            topSellingProductsListEl.innerHTML = '';
            summary.top_selling_products.forEach(product => {
                const productDiv = document.createElement('div');
                topSellingProductsListEl.appendChild(productDiv);

                productDiv.className = 'pb-2 mb-2 border-b border-slate-200 last:border-b-0 last:mb-0 last:pb-0';
                productDiv.innerHTML = `
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-slate-700">${product.product_name_at_purchase || 'N/A'}</span>
                        <span class="text-green -600 font-semibold">${formatPrice(product.total_revenue_from_product)}</span>
                    </div>
                    <span class="text-xs text-slate-500">Quantity Sold: ${product.total_quantity_sold || 0}</span>
                `;
            });
        } else if (topSellingProductsListEl) {
            topSellingProductsListEl.innerHTML = '<p class="italic">No top selling product data available.</p>';
        }

    }

    async function fetchSalesData() {
        try {
            // Path relative to where the HTML page is served (e.g., public/sales/index.php)
            const response = await fetch('../api/get_sales_summary.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            if (data.success) {
                displaySalesSummary(data.summary);
            } else {
                console.error('Failed to fetch sales summary:', data.message);
                salesLoadingDiv.classList.add('hidden');
                noSalesDataMessageDiv.classList.remove('hidden');
                noSalesDataMessageDiv.textContent = `Error: ${data.message || 'Could not load sales data.'}`;
            }
        } catch (error) {
            console.error('Error fetching sales data:', error);
            salesLoadingDiv.classList.add('hidden');
            noSalesDataMessageDiv.classList.remove('hidden');
            noSalesDataMessageDiv.textContent = 'An error occurred while loading sales data. Please try again.';
        }
    }

    printSalesReportBtn.addEventListener('click', function () {
        const startDate = reportStartDateInput.value;
        const endDate = reportEndDateInput.value;

        let reportUrl = 'print_report.php'; // Relative to current page (public/sales/)
        const params = new URLSearchParams();
        if (startDate) {
            params.append('start_date', startDate);
        }
        if (endDate) {
            params.append('end_date', endDate);
        }
        if (params.toString()) {
            reportUrl += `?${params.toString()}`;
        }

        // Open in a new tab. The print_report.php can then trigger window.print()
        window.open(reportUrl, '_blank');
    });


    fetchSalesData(); // Initial fetch
});
