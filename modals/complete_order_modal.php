<!-- Complete Order Modal -->
<div x-show="isCompleteOrderModalOpen" x-cloak
     x-init="console.log('[Complete Order Modal] Initialized by Alpine.')"
     class="fixed inset-0 z-[70] overflow-y-auto" 
     aria-labelledby="complete-order-modal-title" role="dialog" aria-modal="true"
     @keydown.escape.window="isCompleteOrderModalOpen = false">

    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="isCompleteOrderModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity" 
             @click="isCompleteOrderModalOpen = false" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div x-show="isCompleteOrderModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

            <!-- Header -->
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200/50">
                <h3 class="text-lg font-medium text-gray-900" id="complete-order-modal-title">Complete Your Order</h3>
                <button type="button" @click="isCompleteOrderModalOpen = false" 
                        class="bg-white/50 rounded-lg text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2 backdrop-blur-xl">
                    <span class="sr-only">Close</span>
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <!-- Form Content -->
            <form @submit.prevent="prepareAndOpenFinaliseModal()" class="p-6 space-y-5">
                
                <!-- Fulfillment Method -->
                <fieldset>
                    <legend class="block text-sm font-medium text-gray-700 mb-2">How would you like to receive your order?</legend>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center p-3 border rounded-md cursor-pointer transition-colors" 
                               :class="{ 'bg-blue-50 border-blue-300 shadow-sm': orderFulfillmentMethod === 'delivery', 'border-gray-300 hover:bg-gray-50': orderFulfillmentMethod !== 'delivery' }">
                            <input type="radio" name="fulfillment_method" value="delivery" x-model="orderFulfillmentMethod" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                            <i data-lucide="truck" class="w-5 h-5 ml-3" :class="{ 'text-blue-700': orderFulfillmentMethod === 'delivery', 'text-gray-500': orderFulfillmentMethod !== 'delivery' }"></i>
                            <span class="ml-2 text-sm font-medium" :class="{ 'text-blue-800': orderFulfillmentMethod === 'delivery', 'text-gray-700': orderFulfillmentMethod !== 'delivery' }">Delivery</span>
                        </label>
                        <label class="flex items-center p-3 border rounded-md cursor-pointer transition-colors" 
                               :class="{ 'bg-blue-50 border-blue-300 shadow-sm': orderFulfillmentMethod === 'pickup', 'border-gray-300 hover:bg-gray-50': orderFulfillmentMethod !== 'pickup' }">
                            <input type="radio" name="fulfillment_method" value="pickup" x-model="orderFulfillmentMethod" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                            <i data-lucide="package" class="w-5 h-5 ml-3" :class="{ 'text-blue-700': orderFulfillmentMethod === 'pickup', 'text-gray-500': orderFulfillmentMethod !== 'pickup' }"></i>
                            <span class="ml-2 text-sm font-medium" :class="{ 'text-blue-800': orderFulfillmentMethod === 'pickup', 'text-gray-700': orderFulfillmentMethod !== 'pickup' }">Pickup</span>
                        </label>
                    </div>
                </fieldset>
                
                <!-- Delivery Details -->
                <div x-show="orderFulfillmentMethod === 'delivery'" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="space-y-4 border-t pt-4 mt-4">
                    <p class="text-sm font-semibold text-gray-800">Delivery Details</p>
                    <div>
                        <label for="delivery_name" class="block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="delivery_name" id="delivery_name" x-model="customerName" required minlength="2" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="delivery_phone" class="block text-sm font-medium text-gray-700">Phone Number <span class="text-red-500">*</span></label>
                        <input type="tel" name="delivery_phone" id="delivery_phone" x-model="customerPhone" required minlength="7" pattern="[0-9\s\-\+]*" title="Please enter a valid phone number (digits, spaces, -, + allowed)"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="delivery_email" class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="delivery_email" id="delivery_email" x-model="customerEmail" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="delivery_address" class="block text-sm font-medium text-gray-700">Delivery Address <span class="text-red-500">*</span></label>
                        <textarea name="delivery_address" id="delivery_address" rows="3" x-model="customerAddress" 
                                  :required="orderFulfillmentMethod === 'delivery'" minlength="10" 
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                    </div>
                </div>
                
                <!-- Pickup Details -->
                <div x-show="orderFulfillmentMethod === 'pickup'" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="space-y-4 border-t pt-4 mt-4">
                    <p class="text-sm font-semibold text-gray-800">Pickup Details</p>
                    <fieldset>
                        <legend class="block text-sm font-medium text-gray-700 mb-2">Who will be picking up the order?</legend>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center p-3 border rounded-md cursor-pointer text-sm transition-colors" 
                                   :class="{ 'bg-blue-50 border-blue-300 shadow-sm': pickupBy === 'myself', 'border-gray-300 hover:bg-gray-50': pickupBy !== 'myself' }">
                                <input type="radio" name="pickup_by" value="myself" x-model="pickupBy" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                <i data-lucide="user-check" class="w-5 h-5 ml-3" :class="{ 'text-blue-700': pickupBy === 'myself', 'text-gray-500': pickupBy !== 'myself' }"></i>
                                <span class="ml-2" :class="{ 'text-blue-800 font-medium': pickupBy === 'myself', 'text-gray-700': pickupBy !== 'myself' }">Myself</span>
                            </label>
                            <label class="flex items-center p-3 border rounded-md cursor-pointer text-sm transition-colors" 
                                   :class="{ 'bg-blue-50 border-blue-300 shadow-sm': pickupBy === 'someone_else', 'border-gray-300 hover:bg-gray-50': pickupBy !== 'someone_else' }">
                                <input type="radio" name="pickup_by" value="someone_else" x-model="pickupBy" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                <i data-lucide="users" class="w-5 h-5 ml-3" :class="{ 'text-blue-700': pickupBy === 'someone_else', 'text-gray-500': pickupBy !== 'someone_else' }"></i>
                                <span class="ml-2" :class="{ 'text-blue-800 font-medium': pickupBy === 'someone_else', 'text-gray-700': pickupBy !== 'someone_else' }">Someone Else</span>
                            </label>
                        </div>
                    </fieldset>
                    
                    <!-- Fields for Myself / Shared -->
                    <div class="space-y-4 mt-4">
                        <div>
                            <label for="pickup_customer_name" class="block text-sm font-medium text-gray-700">Your Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="pickup_customer_name" id="pickup_customer_name" x-model="customerName" required minlength="2" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="pickup_customer_phone" class="block text-sm font-medium text-gray-700">Your Phone Number <span class="text-red-500">*</span></label>
                            <input type="tel" name="pickup_customer_phone" id="pickup_customer_phone" x-model="customerPhone" required minlength="7" pattern="[0-9\s\-\+]*" title="Please enter a valid phone number (digits, spaces, -, + allowed)"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="pickup_customer_email" class="block text-sm font-medium text-gray-700">Your Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="pickup_customer_email" id="pickup_customer_email" x-model="customerEmail" required 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Fields for Someone Else -->
                    <div x-show="pickupBy === 'someone_else'" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="space-y-4 mt-4 border-t pt-4">
                         <p class="text-xs font-semibold text-gray-800">Pickup Person's Details</p>
                         <div>
                             <label for="pickup_person_name" class="block text-sm font-medium text-gray-700">Their Full Name <span class="text-red-500">*</span></label>
                             <input type="text" name="pickup_person_name" id="pickup_person_name" x-model="pickupPersonName" :required="pickupBy === 'someone_else'" minlength="2" 
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                         </div>
                         <div>
                             <label for="pickup_person_phone" class="block text-sm font-medium text-gray-700">Their Phone Number <span class="text-red-500">*</span></label>
                             <input type="tel" name="pickup_person_phone" id="pickup_person_phone" x-model="pickupPersonPhone" :required="pickupBy === 'someone_else'" minlength="7" pattern="[0-9\s\-\+]*" title="Please enter a valid phone number (digits, spaces, -, + allowed)"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                         </div>
                    </div>
                </div>
                
                 <!-- Footer Actions -->
                <div class="pt-5 flex justify-end gap-3 border-t border-gray-200/50">
                    <button type="button" @click="isCompleteOrderModalOpen = false" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center">
                        <i data-lucide="arrow-right-circle" class="h-5 w-5 mr-1"></i>
                        Place Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 