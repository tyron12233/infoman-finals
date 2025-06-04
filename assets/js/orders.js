// assets/js/orders.js

document.addEventListener('DOMContentLoaded', function () {
    const ordersContainer = document.getElementById('ordersContainer');
    const ordersLoadingDiv = document.getElementById('ordersLoading');
    const noOrdersMessageDiv = document.getElementById('noOrdersMessage');

    if (!ordersContainer || !ordersLoadingDiv || !noOrdersMessageDiv) {
        console.error('Required DOM elements for orders page not found.');
        return;
    }

    function formatPrice(amount) {
        return `P ${parseFloat(amount).toFixed(2)}`;
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

    function getStatusClass(status) {
        const statusKey = status ? status.toLowerCase() : 'unknown';
        switch (statusKey) {
            case 'completed':
            case 'paid': // Assuming 'Paid' is a form of completed
                return 'bg-green-100 text-green-700';
            case 'pending':
                return 'bg-yellow-100 text-yellow-700';
            case 'processing':
                return 'bg-blue-100 text-blue-700';
            case 'cancelled':
                return 'bg-red-100 text-red-700';
            default:
                return 'bg-slate-100 text-slate-700';
        }
    }

    function renderActionButtons(order) {
        let buttonsHtml = '<div class="mt-3 pt-3 border-t border-slate-200 flex flex-wrap gap-2">';
        const currentStatus = order.status ? order.status.toLowerCase() : 'unknown';

        // "Serve" button (marks as Processing or Completed)
        // Let's make "Serve" move it to "Processing", and then a "Complete" button appears.
        if (currentStatus === 'pending') {
            buttonsHtml += `<button class="order-action-btn bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold py-1 px-3 rounded" data-order-id="${order.order_id}" data-new-status="Processing">Mark as Processing</button>`;
        }

        if (currentStatus === 'processing') {
            buttonsHtml += `<button class="order-action-btn bg-green-500 hover:bg-green-600 text-white text-xs font-semibold py-1 px-3 rounded" data-order-id="${order.order_id}" data-new-status="Completed">Mark as Completed (Served)</button>`;
        }

        // Cancel button (if not already completed or cancelled)
        if (currentStatus !== 'completed' && currentStatus !== 'cancelled') {
            buttonsHtml += `<button class="order-action-btn bg-red-500 hover:bg-red-600 text-white text-xs font-semibold py-1 px-3 rounded" data-order-id="${order.order_id}" data-new-status="Cancelled">Cancel Order</button>`;
        }

        if (buttonsHtml === '<div class="mt-3 pt-3 border-t border-slate-200 flex flex-wrap gap-2">') { // No buttons added
            buttonsHtml += '<p class="text-xs text-slate-500 italic">No actions available.</p>';
        }

        buttonsHtml += '</div>';
        return buttonsHtml;
    }


    function displayOrders(orders) {
        ordersLoadingDiv.classList.add('hidden');

        if (!orders || orders.length === 0) {
            noOrdersMessageDiv.classList.remove('hidden');
            return;
        }
        noOrdersMessageDiv.classList.add('hidden');

        // Clear previous orders by removing all children of ordersContainer
        // except for the loading and noOrdersMessage divs themselves.
        let child = ordersContainer.firstChild;
        while (child) {
            let nextChild = child.nextSibling;
            if (child !== ordersLoadingDiv && child !== noOrdersMessageDiv) {
                ordersContainer.removeChild(child);
            }
            child = nextChild;
        }


        orders.forEach(order => {
            const orderCard = document.createElement('div');
            orderCard.className = 'order-card bg-slate-50 border border-slate-200 rounded-lg shadow-md p-4 md:p-6';
            orderCard.id = `order-${order.order_id}`; // Add an ID for easy targeting

            let itemsHtml = '<ul class="list-disc list-inside space-y-1 pl-1 mt-2 text-sm text-slate-600">';
            if (order.items && order.items.length > 0) {
                order.items.forEach(item => {
                    itemsHtml += `<li>${item.quantity} x ${item.product_name_at_purchase} (${formatPrice(item.price_at_purchase)} each)</li>`;
                    if (item.notes && item.notes.trim() !== '') {
                        itemsHtml += `<li class="pl-4 text-xs italic text-slate-500">Note: ${item.notes}</li>`;
                    }
                });
            } else {
                itemsHtml += '<li>No items information available.</li>';
            }
            itemsHtml += '</ul>';

            const statusBadgeId = `status-badge-${order.order_id}`;
            const actionButtonsContainerId = `actions-${order.order_id}`;

            orderCard.innerHTML = `
                <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-700">Order #${order.order_number || order.order_id}</h2>
                        <p class="text-xs text-slate-500">Date: ${formatDate(order.created_at)}</p>
                    </div>
                    <span id="${statusBadgeId}" class="status-badge text-xs font-medium px-2.5 py-0.5 rounded-full mt-2 sm:mt-0 ${getStatusClass(order.status)}">
                        ${order.status ? order.status.charAt(0).toUpperCase() + order.status.slice(1) : 'Unknown'}
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                    <p><strong class="text-slate-600">Customer:</strong> ${order.customer_name || 'N/A'}</p>
                    <p><strong class="text-slate-600">Type:</strong> ${order.order_type || 'N/A'}</p>
                    <p class="md:col-span-2"><strong class="text-slate-600">Total:</strong> <span class="font-semibold text-amber-600">${formatPrice(order.total_amount)}</span></p>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700 mb-1">Items:</h3>
                    ${itemsHtml}
                </div>
                <div id="${actionButtonsContainerId}">
                    ${renderActionButtons(order)}
                </div>
            `;
            ordersContainer.appendChild(orderCard);
        });
    }

    async function handleOrderStatusUpdate(orderId, newStatus) {
        // Disable buttons on the specific card to prevent multiple clicks
        const orderCard = document.getElementById(`order-${orderId}`);
        if (orderCard) {
            orderCard.querySelectorAll('.order-action-btn').forEach(btn => btn.disabled = true);
        }

        try {
            const response = await fetch('../api/update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ order_id: orderId, new_status: newStatus })
            });

            const data = await response.json();

            if (data.success) {
                // Update UI dynamically
                if (orderCard) {
                    const statusBadge = orderCard.querySelector(`#status-badge-${orderId}`);
                    if (statusBadge) {
                        statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                        statusBadge.className = `status-badge text-xs font-medium px-2.5 py-0.5 rounded-full mt-2 sm:mt-0 ${getStatusClass(newStatus)}`;
                    }
                    // Re-render action buttons for this specific order
                    const actionsContainer = orderCard.querySelector(`#actions-${orderId}`);
                    if (actionsContainer) {
                        // Create a mock order object with the new status to re-render buttons
                        const updatedOrderForButtons = { order_id: orderId, status: newStatus };
                        actionsContainer.innerHTML = renderActionButtons(updatedOrderForButtons);
                    }
                }
                // Potentially show a success dialog from dashboard.js's showCustomDialog
                if (typeof showCustomDialog === 'function') {
                    showCustomDialog('Status Updated', `Order #${orderId} status updated to ${newStatus}.`, 'success');
                } else {
                    alert(`Order #${orderId} status updated to ${newStatus}.`);
                }
            } else {
                if (typeof showCustomDialog === 'function') {
                    showCustomDialog('Update Failed', data.message || 'Could not update order status.', 'error');
                } else {
                    alert(`Failed to update order status: ${data.message || 'Unknown error.'}`);
                }
                // Re-enable buttons if update failed and card exists
                if (orderCard) {
                    orderCard.querySelectorAll('.order-action-btn').forEach(btn => btn.disabled = false);
                }
            }
        } catch (error) {
            console.error('Error updating order status:', error);
            if (typeof showCustomDialog === 'function') {
                showCustomDialog('Update Error', 'An error occurred while updating status. Please try again.', 'error');
            } else {
                alert('An error occurred. Please try again.');
            }
            // Re-enable buttons if update failed and card exists
            if (orderCard) {
                orderCard.querySelectorAll('.order-action-btn').forEach(btn => btn.disabled = false);
            }
        }
    }

    ordersContainer.addEventListener('click', function (event) {
        if (event.target.classList.contains('order-action-btn')) {
            const orderId = parseInt(event.target.dataset.orderId);
            const newStatus = event.target.dataset.newStatus;
            if (orderId && newStatus) {
                handleOrderStatusUpdate(orderId, newStatus);
            }
        }
    });

    function fetchOrders() {
        ordersLoadingDiv.classList.remove('hidden');
        noOrdersMessageDiv.classList.add('hidden');
        // Clear existing order cards before fetching new ones
        let child = ordersContainer.firstChild;
        while (child) {
            let nextChild = child.nextSibling;
            if (child !== ordersLoadingDiv && child !== noOrdersMessageDiv) {
                ordersContainer.removeChild(child);
            }
            child = nextChild;
        }


        fetch('../api/get_orders.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayOrders(data.orders);
                } else {
                    console.error('Failed to fetch orders:', data.message);
                    ordersLoadingDiv.classList.add('hidden');
                    noOrdersMessageDiv.classList.remove('hidden');
                    noOrdersMessageDiv.textContent = `Error: ${data.message || 'Could not load orders.'}`;
                }
            })
            .catch(error => {
                console.error('Error fetching orders:', error);
                ordersLoadingDiv.classList.add('hidden');
                noOrdersMessageDiv.classList.remove('hidden');
                noOrdersMessageDiv.textContent = 'An error occurred while loading orders. Please try again.';
            });
    }

    fetchOrders(); // Initial fetch
});
