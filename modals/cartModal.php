<!-- Cart Modal -->
<div id="cartModal" 
     class="fixed inset-0 z-50 overflow-y-auto hidden" 
     aria-labelledby="cart-modal-title" role="dialog" aria-modal="true">
    
    <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div id="cartModalOverlay"
             class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity ease-out duration-300 opacity-0"
             aria-hidden="true"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div id="cartModalPanel"
             class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all ease-out duration-300 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 sm:my-8 sm:max-w-lg w-full">
            
            <!-- Header -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="cart-modal-title">
                    Shopping Cart
                </h3>
                <button type="button" id="closeCartModalButton" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Cart Content -->
            <div class="px-4 py-5 sm:p-6">
                <div id="cartModalItemsContainer" class="max-h-96 overflow-y-auto divide-y divide-gray-200 pr-2">
                    <!-- Cart items will be injected here by JS -->
                </div>
                
                <!-- Empty Cart Message (Moved outside items container) -->
                 <div id="cartModalEmptyMsg" class="text-center py-12" style="display: none;"> <!-- Start hidden via style -->
                    <!-- Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <!-- Message -->
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Cart is Empty</h3>
                    <p class="mt-1 text-sm text-gray-500">Add some items from the store.</p>
                    <!-- Link/Button -->
                    <div class="mt-6">
                        <a href="/pages/products.php" 
                           id="cartModalBrowseButton"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Browse Products
                        </a>
                    </div>
                </div>
                 
                 <!-- Loading Message (Moved outside items container) -->
                 <div id="cartModalLoadingMsg" class="text-center text-gray-500 py-8">Loading cart...</div> 
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-4 py-4 sm:px-6 border-t border-gray-200">
                <!-- Cart Total -->
                <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-medium text-gray-900">Subtotal:</span>
                    <span id="cartModalSubtotal" class="text-lg font-bold text-gray-900">₦0.00</span>
                </div>
                <!-- Action Buttons -->
                <div class="flex flex-row gap-3">
                    <button type="button" id="cartModalProceedButton" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-auto disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
                            disabled> 
                        Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 