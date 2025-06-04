// assets/js/products.js

document.addEventListener('DOMContentLoaded', function () {
    const productsTableBody = document.getElementById('productsTableBody');
    const productsTable = document.getElementById('productsTable');
    const productsLoadingDiv = document.getElementById('productsLoading');
    const noProductsMessageDiv = document.getElementById('noProductsMessage');

    const addProductBtn = document.getElementById('addProductBtn');
    const productModal = document.getElementById('productModal');
    const closeProductModalBtn = document.getElementById('closeProductModalBtn');
    const cancelProductFormBtn = document.getElementById('cancelProductFormBtn');
    const productForm = document.getElementById('productForm');
    const modalTitle = document.getElementById('modalTitle');
    const variantsContainer = document.getElementById('variantsContainer');
    const addVariantBtn = document.getElementById('addVariantBtn');
    const productCategorySelect = document.getElementById('productCategory');

    let allCategories = []; // To store categories for the form

    // --- Modal Handling ---
    function openModal(title = 'Add New Product', action = 'add', product = null) {
        modalTitle.textContent = title;
        productForm.reset(); // Reset form fields
        document.getElementById('formAction').value = action;
        document.getElementById('productId').value = '';
        variantsContainer.innerHTML = ''; // Clear existing variants

        // Populate categories
        productCategorySelect.innerHTML = '<option value="">Select Category</option>';
        allCategories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            productCategorySelect.appendChild(option);
        });

        console.log("ASDKSAKDASD", action, product);

        if ((action === 'edit' || action === 'update') && product) {
            document.getElementById('productId').value = product.product_id;
            document.getElementById('productName').value = product.product_name || '';
            document.getElementById('productDescription').value = product.product_description || '';
            document.getElementById('productImageUrl').value = product.image_url || '';
            document.getElementById('productCategory').value = product.category_id || '';
            document.getElementById('productDisplayOrder').value = product.product_display_order || 0;

            if (product.variants && product.variants.length > 0) {
                product.variants.forEach(variant => addVariantInputGroup(variant));
            } else {
                addVariantInputGroup(); // Add one empty variant if none exist for editing
            }
        } else {
            addVariantInputGroup(); // Add one empty variant for new product
        }

        productModal.classList.remove('hidden');
        setTimeout(() => {
            productModal.classList.remove('opacity-0');
            productModal.querySelector('.dialog-content').classList.remove('scale-95');
            productModal.querySelector('.dialog-content').classList.add('scale-100');
        }, 10);
    }

    function closeModal() {
        productModal.classList.add('opacity-0');
        productModal.querySelector('.dialog-content').classList.remove('scale-100');
        productModal.querySelector('.dialog-content').classList.add('scale-95');
        setTimeout(() => productModal.classList.add('hidden'), 300);
    }

    if (addProductBtn) addProductBtn.addEventListener('click', () => openModal('Add New Product', 'add'));
    if (closeProductModalBtn) closeProductModalBtn.addEventListener('click', closeModal);
    if (cancelProductFormBtn) cancelProductFormBtn.addEventListener('click', closeModal);
    if (productModal) { // Close on overlay click
        productModal.addEventListener('click', (event) => {
            if (event.target === productModal) closeModal();
        });
    }


    // --- Variant Input Management ---
    let variantCounter = 0;
    function addVariantInputGroup(variant = null) {
        variantCounter++;
        const variantDiv = document.createElement('div');
        variantDiv.className = 'variant-group border p-3 rounded-md bg-slate-50 relative';
        variantDiv.innerHTML = `
            <input type="hidden" name="variants[${variantCounter}][id]" value="${variant ? variant.variant_id || '' : ''}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Variant Name <span class="text-red-500">*</span></label>
                    <input type="text" name="variants[${variantCounter}][name]" value="${variant ? variant.variant_name || '' : ''}" required class="w-full p-1.5 border border-slate-300 rounded-md text-sm focus:ring-1 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Price <span class="text-red-500">*</span></label>
                    <input type="number" name="variants[${variantCounter}][price]" value="${variant ? variant.variant_price || '' : ''}" required step="0.01" min="0" class="w-full p-1.5 border border-slate-300 rounded-md text-sm focus:ring-1 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">SKU</label>
                    <input type="text" name="variants[${variantCounter}][sku]" value="${variant ? variant.variant_sku || '' : ''}" class="w-full p-1.5 border border-slate-300 rounded-md text-sm focus:ring-1 focus:ring-amber-500">
                </div>
            </div>
            ${variantCounter > 1 || (variantsContainer.querySelectorAll('.variant-group').length > 0 && !variant) ? // Show remove button if more than one, or if it's a newly added one to an existing list
                '<button type="button" class="remove-variant-btn absolute top-1 right-1 text-red-500 hover:text-red-700 text-xs p-1 bg-white rounded-full leading-none">&times;</button>' : ''}
        `;
        variantsContainer.appendChild(variantDiv);

        variantDiv.querySelector('.remove-variant-btn')?.addEventListener('click', function () {
            variantDiv.remove();
            // Ensure at least one variant group remains if all are removed
            if (variantsContainer.querySelectorAll('.variant-group').length === 0) {
                addVariantInputGroup();
            }
        });
    }
    if (addVariantBtn) addVariantBtn.addEventListener('click', () => addVariantInputGroup());


    // --- Fetch and Display Products ---
    async function fetchProducts() {
        if (!productsLoadingDiv || !productsTable || !noProductsMessageDiv || !productsTableBody) return;
        productsLoadingDiv.classList.remove('hidden');
        productsTable.classList.add('hidden');
        noProductsMessageDiv.classList.add('hidden');

        try {
            const response = await fetch('../api/manage_product.php'); // GET request
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();

            productsLoadingDiv.classList.add('hidden');
            if (data.success) {
                allCategories = data.categories || []; // Store categories
                if (data.products && data.products.length > 0) {
                    productsTable.classList.remove('hidden');
                    productsTableBody.innerHTML = ''; // Clear existing rows
                    data.products.forEach(product => {
                        const row = productsTableBody.insertRow();
                        row.innerHTML = `
                            <td class="py-2 px-4 border-b border-slate-200">
                                <img src="${product.image_url || 'https://via.placeholder.com/50'}" alt="${product.product_name}" class="w-10 h-10 object-cover rounded">
                            </td>
                            <td class="py-2 px-4 border-b border-slate-200">${product.product_name}</td>
                            <td class="py-2 px-4 border-b border-slate-200">${product.category_name || 'N/A'}</td>
                            <td class="py-2 px-4 border-b border-slate-200 text-xs">
                                ${product.variants && product.variants.length > 0 ?
                                product.variants.map(v => `${v.variant_name} (P${parseFloat(v.variant_price).toFixed(2)})`).join('<br>') :
                                'No variants'}
                            </td>
                            <td class="py-2 px-4 border-b border-slate-200">
                                <button class="edit-product-btn text-blue-500 hover:text-blue-700 text-xs mr-2" data-product-id="${product.product_id}">Edit</button>
                                <button class="delete-product-btn text-red-500 hover:text-red-700 text-xs" data-product-id="${product.product_id}" data-product-name="${product.product_name}">Delete</button>
                            </td>
                        `;
                    });
                } else {
                    noProductsMessageDiv.classList.remove('hidden');
                }
            } else {
                noProductsMessageDiv.classList.remove('hidden');
                noProductsMessageDiv.textContent = `Error: ${data.message || 'Could not load products.'}`;
            }
        } catch (error) {
            console.error('Error fetching products:', error);
            productsLoadingDiv.classList.add('hidden');
            noProductsMessageDiv.classList.remove('hidden');
            noProductsMessageDiv.textContent = 'An error occurred while loading products.';
        }
    }

    // --- Handle Product Form Submission (Add/Edit) ---
    if (productForm) {
        productForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const formData = new FormData(productForm);
            const action = formData.get('action');
            const productId = formData.get('product_id');

            const productPayload = {
                name: formData.get('name'),
                description: formData.get('description'),
                image_url: formData.get('image_url'),
                category_id: formData.get('category_id'),
                display_order: formData.get('display_order')
            };

            const variantsPayload = [];
            const variantGroups = variantsContainer.querySelectorAll('.variant-group');
            variantGroups.forEach((group, index) => { // Use index from querySelectorAll for consistent naming if variantCounter has gaps
                const variantIdInput = group.querySelector(`input[name^="variants["][name$="[id]"]`);
                const variantNameInput = group.querySelector(`input[name^="variants["][name$="[name]"]`);
                const variantPriceInput = group.querySelector(`input[name^="variants["][name$="[price]"]`);
                const variantSkuInput = group.querySelector(`input[name^="variants["][name$="[sku]"]`);

                if (variantNameInput && variantNameInput.value.trim() !== '' && variantPriceInput && variantPriceInput.value.trim() !== '') {
                    variantsPayload.push({
                        id: variantIdInput ? variantIdInput.value : null,
                        name: variantNameInput.value,
                        price: variantPriceInput.value,
                        sku: variantSkuInput ? variantSkuInput.value : null
                    });
                }
            });

            if (variantsPayload.length === 0) {
                if (typeof showCustomDialog === 'function') {
                    showCustomDialog('Validation Error', 'At least one valid product variant is required.', 'warning');
                } else {
                    alert('At least one valid product variant is required.');
                }
                return;
            }


            const payload = {
                action: action,
                product: productPayload,
                variants: variantsPayload
            };
            if (action === 'update' && productId) {
                payload.product_id = productId;
            }

            const saveButton = document.getElementById('saveProductBtn');
            saveButton.disabled = true;
            saveButton.textContent = 'Saving...';

            try {
                const response = await fetch('../api/manage_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();

                if (data.success) {
                    closeModal();
                    fetchProducts(); // Refresh the product list
                    if (typeof showCustomDialog === 'function') {
                        showCustomDialog('Success', data.message, 'success');
                    } else {
                        alert(data.message);
                    }
                } else {
                    if (typeof showCustomDialog === 'function') {
                        showCustomDialog('Error', data.message || 'Failed to save product.', 'error');
                    } else {
                        alert(data.message || 'Failed to save product.');
                    }
                }
            } catch (error) {
                console.error('Error saving product:', error);
                if (typeof showCustomDialog === 'function') {
                    showCustomDialog('Error', 'An error occurred. Please try again.', 'error');
                } else {
                    alert('An error occurred. Please try again.');
                }
            } finally {
                saveButton.disabled = false;
                saveButton.textContent = 'Save Product';
            }
        });
    }

    // --- Handle Edit Product ---
    if (productsTableBody) {
        productsTableBody.addEventListener('click', async function (event) {
            if (event.target.classList.contains('edit-product-btn')) {
                const productId = event.target.dataset.productId;
                try {
                    // Fetch the specific product details for editing
                    const response = await fetch(`../api/manage_product.php?id=${productId}`);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const data = await response.json();
                    if (data.success && data.product) {
                        openModal('Edit Product', 'update', data.product);
                    } else {
                        if (typeof showCustomDialog === 'function') {
                            showCustomDialog('Error', data.message || 'Could not load product details for editing.', 'error');
                        } else {
                            alert(data.message || 'Could not load product details for editing.');
                        }
                    }
                } catch (error) {
                    console.error('Error fetching product for edit:', error);
                    if (typeof showCustomDialog === 'function') {
                        showCustomDialog('Error', 'Failed to load product details.', 'error');
                    } else {
                        alert('Failed to load product details.');
                    }
                }
            }
        });
    }

    // --- Handle Delete Product ---
    if (productsTableBody) {
        productsTableBody.addEventListener('click', async function (event) {
            if (event.target.classList.contains('delete-product-btn')) {
                const productId = event.target.dataset.productId;
                const productName = event.target.dataset.productName;

                // Replace window.confirm with custom dialog if available and preferred
                let confirmed = false;
                if (typeof showCustomDialog === 'function') {
                    // This requires showCustomDialog to be adapted for confirm/cancel behavior
                    // For now, using window.confirm for simplicity of this example part
                    confirmed = window.confirm(`Are you sure you want to delete the product "${productName}"? This action cannot be undone.`);
                } else {
                    confirmed = window.confirm(`Are you sure you want to delete the product "${productName}"? This action cannot be undone.`);
                }

                if (confirmed) {
                    try {
                        const response = await fetch('../api/manage_product.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ action: 'delete', product_id: productId })
                        });
                        const data = await response.json();
                        if (data.success) {
                            fetchProducts(); // Refresh list
                            if (typeof showCustomDialog === 'function') {
                                showCustomDialog('Deleted', data.message, 'success');
                            } else {
                                alert(data.message);
                            }
                        } else {
                            if (typeof showCustomDialog === 'function') {
                                showCustomDialog('Error', data.message || 'Failed to delete product.', 'error');
                            } else {
                                alert(data.message || 'Failed to delete product.');
                            }
                        }
                    } catch (error) {
                        console.error('Error deleting product:', error);
                        if (typeof showCustomDialog === 'function') {
                            showCustomDialog('Error', 'An error occurred. Please try again.', 'error');
                        } else {
                            alert('An error occurred. Please try again.');
                        }
                    }
                }
            }
        });
    }

    // Initial fetch of products
    fetchProducts();
});
