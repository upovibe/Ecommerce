<?php
// Product modal template
// This modal will be populated dynamically with JavaScript
?>
<div id="productModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- This element centers the modal content -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <!-- Close button -->
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" id="closeProductModal" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal content -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="w-full">
                        <!-- Product details will be loaded here -->
                        <div id="productModalContent" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product image -->
                            <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded-lg overflow-hidden">
                                <img id="modalProductImage" src="" alt="Product image" class="w-full h-full object-center object-cover">
                            </div>
                            
                            <!-- Product info -->
                            <div>
                                <h2 id="modalProductName" class="text-2xl font-bold text-gray-900 mb-2"></h2>
                                <p id="modalProductPrice" class="text-xl font-semibold text-primary mb-4"></p>
                                
                                <div id="modalProductDescription" class="prose prose-sm text-gray-500 mb-6"></div>
                                
                                <!-- Options container (e.g., color, size) - populated dynamically if needed -->
                                <div id="productOptionsContainer" class="mb-6"></div>
                                
                                <!-- Quantity selector -->
                                <div class="flex items-center mb-6">
                                    <span class="mr-3 text-sm font-medium text-gray-700">Quantity</span>
                                    <div class="flex items-center border border-gray-300 rounded">
                                        <button type="button" id="decreaseQuantity" class="px-3 py-1 text-gray-600 hover:bg-gray-100">
                                            -
                                        </button>
                                        <input type="number" id="productQuantity" class="w-12 text-center border-0 focus:ring-0" value="1" min="1">
                                        <button type="button" id="increaseQuantity" class="px-3 py-1 text-gray-600 hover:bg-gray-100">
                                            +
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Add to cart button -->
                                <button type="button" id="addToCartBtn" class="w-full bg-primary text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Store current product data
    let currentProduct = null;
    
    // Function to open the product modal with product data
    function openProductModal(productData) {
        // Store product data for reference
        currentProduct = productData;
        
        // Set product details in modal
        document.getElementById('modalProductImage').src = productData.image;
        document.getElementById('modalProductName').textContent = productData.name;
        document.getElementById('modalProductPrice').textContent = '<?= STORE_SETTINGS['currency_symbol'] ?? '₦' ?>' + parseFloat(productData.price).toFixed(2);
        document.getElementById('modalProductDescription').innerHTML = productData.description;
        
        // Reset quantity
        document.getElementById('productQuantity').value = 1;
        
        // Populate product options if they exist
        const optionsContainer = document.getElementById('productOptionsContainer');
        optionsContainer.innerHTML = '';
        
        if (productData.options && Object.keys(productData.options).length > 0) {
            for (const [optionName, optionValues] of Object.entries(productData.options)) {
                const optionLabel = document.createElement('div');
                optionLabel.className = 'mb-3';
                optionLabel.innerHTML = `
                    <span class="block text-sm font-medium text-gray-700 mb-2">${optionName.charAt(0).toUpperCase() + optionName.slice(1)}</span>
                    <div class="flex flex-wrap gap-2" data-option-name="${optionName}">
                        ${optionValues.map(value => `
                            <button type="button" class="option-value px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50" 
                                    data-value="${value}">
                                ${value}
                            </button>
                        `).join('')}
                    </div>
                `;
                optionsContainer.appendChild(optionLabel);
            }
            
            // Add event listeners to option buttons
            document.querySelectorAll('.option-value').forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from siblings
                    const parent = this.parentElement;
                    parent.querySelectorAll('.option-value').forEach(btn => {
                        btn.classList.remove('bg-primary', 'text-white');
                        btn.classList.add('text-gray-700', 'border-gray-300');
                    });
                    
                    // Add active class to selected option
                    this.classList.remove('text-gray-700', 'border-gray-300');
                    this.classList.add('bg-primary', 'text-white');
                });
            });
        }
        
        // Show the modal
        document.getElementById('productModal').classList.remove('hidden');
    }
    
    // Close modal
    document.getElementById('closeProductModal').addEventListener('click', function() {
        document.getElementById('productModal').classList.add('hidden');
    });
    
    // Quantity controls
    document.getElementById('decreaseQuantity').addEventListener('click', function() {
        const quantityInput = document.getElementById('productQuantity');
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });
    
    document.getElementById('increaseQuantity').addEventListener('click', function() {
        const quantityInput = document.getElementById('productQuantity');
        quantityInput.value = parseInt(quantityInput.value) + 1;
    });
    
    // Prevent manual entry of invalid quantities
    document.getElementById('productQuantity').addEventListener('change', function() {
        if (this.value < 1) {
            this.value = 1;
        }
    });
    
    // Add to cart button
    document.getElementById('addToCartBtn').addEventListener('click', function() {
        if (!currentProduct) return;
        
        const quantity = parseInt(document.getElementById('productQuantity').value);
        
        // Collect selected options
        const selectedOptions = {};
        document.querySelectorAll('#productOptionsContainer > div > div').forEach(optionGroup => {
            const optionName = optionGroup.dataset.optionName;
            const selectedOption = optionGroup.querySelector('.bg-primary');
            if (selectedOption) {
                selectedOptions[optionName] = selectedOption.dataset.value;
            }
        });
        
        // Add to cart via AJAX
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('product_id', currentProduct.id);
        formData.append('name', currentProduct.name);
        formData.append('price', currentProduct.price);
        formData.append('quantity', quantity);
        formData.append('image', currentProduct.image);
        formData.append('options', JSON.stringify(selectedOptions));
        
        fetch('/utils/cart_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                document.getElementById('productModal').classList.add('hidden');
                
                // Update cart count in navbar
                if (data.cart_count) {
                    const cartCountElements = document.querySelectorAll('.cart-count');
                    cartCountElements.forEach(element => {
                        element.textContent = data.cart_count;
                        element.classList.remove('hidden');
                    });
                }
                
                // Show success notification
                alert('Product added to cart!');
            } else {
                alert('Error adding product to cart: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
</script> 