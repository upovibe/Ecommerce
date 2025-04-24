<!-- Thank You Modal -->
<div x-show="isThankYouModalOpen" x-cloak
     class="fixed inset-0 z-[100] overflow-y-auto"
     aria-labelledby="thank-you-modal-title" role="dialog" aria-modal="true"
     @keydown.escape.window="isThankYouModalOpen = false">

    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="isThankYouModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-md transition-opacity"
             @click="isThankYouModalOpen = false" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div x-show="isThankYouModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">

            <!-- Header -->
            <div class="flex justify-between items-center px-6 py-4 bg-green-50 border-b border-green-200">
                <h3 class="text-lg font-semibold text-green-800 flex items-center" id="thank-you-modal-title">
                    <i data-lucide="check-circle" class="h-6 w-6 mr-2 text-green-600"></i>
                    Order Sent!
                </h3>
                <button type="button" @click="isThankYouModalOpen = false"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 rounded-full p-1">
                    <span class="sr-only">Close</span>
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-700">
                    Thank you! Your order details have been sent to WhatsApp.
                </p>
                <div x-show="finalOrderDetailsForThankYou && finalOrderDetailsForThankYou.orderRef" class="p-4 bg-gray-100 rounded-md border border-gray-200">
                    <p class="text-sm font-medium text-gray-800">Your Order Reference:</p>
                    <p class="text-lg font-bold text-gray-900" x-text="finalOrderDetailsForThankYou.orderRef"></p>
                </div>
                 <p class="text-sm text-gray-600">
                    Please follow the instructions in the WhatsApp chat to confirm and complete your payment.
                </p>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-right">
                <button type="button" @click="isThankYouModalOpen = false"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                    Close
                </button>
            </div>

        </div>
    </div>
</div> 