<!-- Finalise Order Modal -->
<div x-show="isFinaliseOrderModalOpen" x-cloak
     class="fixed inset-0 z-[80] overflow-y-auto" 
     aria-labelledby="finalise-order-modal-title" role="dialog" aria-modal="true"
     @keydown.escape.window="isFinaliseOrderModalOpen = false">

    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="isFinaliseOrderModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm transition-opacity" 
             @click="isFinaliseOrderModalOpen = false" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div x-show="isFinaliseOrderModalOpen" x-transition
             class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

            <!-- Header -->
            <div class="flex justify-between items-center px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900" id="finalise-order-modal-title">Confirm Your Order</h3>
                <button type="button" @click="isFinaliseOrderModalOpen = false" 
                        class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 rounded-full p-1">
                    <span class="sr-only">Close</span>
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <!-- Order Summary Content -->
            <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto" x-show="finalOrderDetails">
                
                <!-- Cart Items Summary -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <h4 class="text-base font-semibold text-gray-800 px-4 py-2 bg-gray-50 border-b border-gray-200">Order Items</h4>
                    <div class="max-h-60 overflow-y-auto">
                        <template x-for="(item, id) in finalOrderDetails?.cart" :key="id">
                            <div class="flex items-center p-3 border-b border-gray-100 last:border-b-0">
                                <img :src="item.details.image || '/assets/images/placeholder.png'" :alt="item.details.name" class="w-12 h-12 object-cover rounded-md flex-shrink-0 mr-3">
                                <div class="flex-grow">
                                    <p class="text-sm font-medium text-gray-900" x-text="item.details.name"></p>
                                    <p class="text-xs text-gray-600">
                                        <span x-text="item.quantity + ' x '"></span>
                                        <span x-text="currencySymbol + parseFloat(item.details.price).toFixed(2)"></span>
                                    </p>
                                    <!-- Display Options -->
                                    <div x-show="item.details.options && Object.keys(item.details.options).length > 0" class="mt-0.5 space-x-1.5 text-xs text-gray-500">
                                        <template x-for="(value, name) in item.details.options" :key="name">
                                            <span class="inline-flex items-center">
                                                <span class="capitalize" x-text="name + ': '"></span><strong class="ml-0.5" x-text="value"></strong>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                <p class="text-sm font-medium text-gray-800 ml-3" x-text="currencySymbol + (item.quantity * parseFloat(item.details.price)).toFixed(2)"></p>
                            </div>
                        </template>
                    </div>
                    <!-- Total -->
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-200 flex justify-end items-center">
                        <span class="text-sm font-medium text-gray-600 mr-2">Total:</span>
                        <span class="text-lg font-semibold text-gray-900"
                              x-text="currencySymbol + Object.values(finalOrderDetails?.cart || {}).reduce((total, item) => total + (parseFloat(item.details.price) * item.quantity), 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })">
                        </span>
                    </div>
                </div>

                <!-- Fulfillment & Customer Details (Accordion) -->
                <div class="border border-gray-200 rounded-lg" x-data="{ open: true }">
                    <div @click="open = !open" class="flex justify-between items-center px-4 py-3 bg-gray-50 border-b border-gray-200 cursor-pointer">
                        <h4 class="text-base font-semibold text-gray-800">Customer & Fulfillment Details</h4>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': open }"></i>
                    </div>
                    <div x-show="open" x-collapse>
                        <dl class="divide-y divide-gray-100 px-4 py-3 text-sm">
                            <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="font-medium text-gray-600 flex items-center">
                                    <i data-lucide="clipboard-list" class="w-4 h-4 mr-2 text-gray-400"></i>Method
                                </dt>
                                <dd class="mt-1 text-gray-800 sm:mt-0 sm:col-span-2 capitalize" x-text="finalOrderDetails?.fulfillment"></dd>
                            </div>
                            <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="font-medium text-gray-600 flex items-center">
                                    <i data-lucide="user" class="w-4 h-4 mr-2 text-gray-400"></i>Name
                                </dt>
                                <dd class="mt-1 text-gray-800 sm:mt-0 sm:col-span-2" x-text="finalOrderDetails?.customer?.name || '-'"></dd>
                            </div>
                            <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="font-medium text-gray-600 flex items-center">
                                    <i data-lucide="phone" class="w-4 h-4 mr-2 text-gray-400"></i>Phone
                                </dt>
                                <dd class="mt-1 text-gray-800 sm:mt-0 sm:col-span-2" x-text="finalOrderDetails?.customer?.phone || '-'"></dd>
                            </div>
                            <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="font-medium text-gray-600 flex items-center">
                                    <i data-lucide="mail" class="w-4 h-4 mr-2 text-gray-400"></i>Email
                                </dt>
                                <dd class="mt-1 text-gray-800 sm:mt-0 sm:col-span-2" x-text="finalOrderDetails?.customer?.email || '-'"></dd>
                            </div>
                            <template x-if="finalOrderDetails?.fulfillment === 'delivery'">
                                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="font-medium text-gray-600 flex items-center">
                                        <i data-lucide="map-pin" class="w-4 h-4 mr-2 text-gray-400"></i>Address
                                    </dt>
                                    <dd class="mt-1 text-gray-800 sm:mt-0 sm:col-span-2 whitespace-pre-line" x-text="finalOrderDetails?.customer?.address || '-'"></dd>
                                </div>
                            </template>
                            <template x-if="finalOrderDetails?.pickup">
                                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="font-medium text-gray-600 flex items-center">
                                        <i data-lucide="users" class="w-4 h-4 mr-2 text-gray-400"></i>Pickup Person
                                    </dt>
                                    <dd class="mt-1 text-gray-800 sm:mt-0 sm:col-span-2">
                                        <span x-text="finalOrderDetails?.pickup?.name || '-'"></span> (<span x-text="finalOrderDetails?.pickup?.phone || '-'"></span>)
                                    </dd>
                                </div>
                            </template>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                <button type="button" @click="isFinaliseOrderModalOpen = false; isCompleteOrderModalOpen = true;" 
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                    Go Back
                </button>
                <button type="button" 
                        @click="isFinaliseOrderModalOpen = false; isCheckoutMethodsModalOpen = true;"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors flex items-center">
                    <i data-lucide="arrow-right" class="w-5 h-5 mr-2"></i>
                    Choose Checkout Method
                </button>
            </div>
        </div>
    </div>
</div> 