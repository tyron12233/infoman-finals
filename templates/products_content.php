<?php
// templates/products_content.php
?>


<script src="<?php echo BASE_URL; ?>assets/js/products.js" defer></script>

<main class="main-content flex-grow p-4 md:p-6 bg-white overflow-y-auto flex flex-col order-2 lg:order-none h-auto">
    <header class="flex justify-between items-center mb-6 pb-3 border-b border-slate-200">
        <h1 class="text-2xl font-semibold text-slate-700">Manage Products</h1>
        <button id="addProductBtn"
            class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded shadow">
            Add New Product
        </button>
    </header>

    <section id="productsTableContainer">
        <div id="productsLoading" class="text-center py-10">
            <p class="text-slate-500 text-lg">Loading products...</p>
            <svg class="animate-spin h-8 w-8 text-slate-500 mx-auto mt-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>
        <div id="noProductsMessage" class="hidden text-center py-10">
            <p class="text-slate-500 text-lg">No products found. Click "Add New Product" to get started.</p>
        </div>
        <table id="productsTable" class="hidden min-w-full bg-white border border-slate-200">
            <thead class="bg-slate-100">
                <tr>
                    <th
                        class="py-3 px-4 border-b text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        Image</th>
                    <th
                        class="py-3 px-4 border-b text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        Name</th>
                    <th
                        class="py-3 px-4 border-b text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        Category</th>
                    <th
                        class="py-3 px-4 border-b text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        Variants</th>
                    <th
                        class="py-3 px-4 border-b text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        Actions</th>
                </tr>
            </thead>
            <tbody id="productsTableBody" class="text-slate-700 text-sm">
                <!-- Product rows will be inserted here by JavaScript -->
            </tbody>
        </table>
    </section>

    <!-- Add/Edit Product Modal -->
    <div id="productModal"
        class="fixed inset-0 bg-slate-800 bg-opacity-75 flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-300 ease-in-out">
        <div
            class="dialog-content bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl transform transition-all duration-300 ease-in-out scale-95 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 id="modalTitle" class="text-xl font-semibold text-slate-700">Add New Product</h3>
                <button id="closeProductModalBtn"
                    class="text-slate-400 hover:text-slate-600 text-2xl focus:outline-none">&times;</button>
            </div>
            <form id="productForm" class="space-y-4">
                <input type="hidden" id="productId" name="product_id">
                <input type="hidden" id="formAction" name="action" value="add">

                <div>
                    <label for="productName" class="block text-sm font-medium text-slate-700 mb-1">Product Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="productName" name="name" required
                        class="w-full p-2 border border-slate-300 rounded-md focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label for="productDescription"
                        class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <textarea id="productDescription" name="description" rows="3"
                        class="w-full p-2 border border-slate-300 rounded-md focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none"></textarea>
                </div>
                <div>
                    <label for="productImageUrl" class="block text-sm font-medium text-slate-700 mb-1">Image URL</label>
                    <input type="url" id="productImageUrl" name="image_url" placeholder="https://example.com/image.jpg"
                        class="w-full p-2 border border-slate-300 rounded-md focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label for="productCategory" class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                    <select id="productCategory" name="category_id"
                        class="w-full p-2 border border-slate-300 rounded-md focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none">
                        <option value="">Select Category</option>
                        <!-- Categories will be populated by JS -->
                    </select>
                </div>
                <div>
                    <label for="productDisplayOrder" class="block text-sm font-medium text-slate-700 mb-1">Display
                        Order</label>
                    <input type="number" id="productDisplayOrder" name="display_order" value="0"
                        class="w-full p-2 border border-slate-300 rounded-md focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>

                <hr class="my-4">
                <h4 class="text-md font-semibold text-slate-700 mb-2">Product Variants <span
                        class="text-red-500">*</span></h4>
                <div id="variantsContainer" class="space-y-3">
                    <!-- Variant input groups will be added here by JS -->
                </div>
                <button type="button" id="addVariantBtn"
                    class="mt-2 text-sm bg-slate-200 hover:bg-slate-300 text-slate-700 py-1 px-3 rounded">Add
                    Variant</button>

                <div class="flex justify-end gap-3 pt-4 border-t mt-6">
                    <button type="button" id="cancelProductFormBtn"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2 px-4 rounded border border-slate-300">Cancel</button>
                    <button type="submit" id="saveProductBtn"
                        class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded">Save
                        Product</button>
                </div>
            </form>
        </div>
    </div>
</main>