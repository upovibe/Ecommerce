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
            class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity"
            @click="isThankYouModalOpen = false" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div x-show="isThankYouModalOpen"
            x-transition:enter="ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-10 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-10 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

            <!-- Confetti animation container -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <template x-for="i in 20" :key="i">
                    <div class="absolute confetti-particle"
                        :class="['bg-' + ['blue','green','yellow','pink','purple'][Math.floor(Math.random() * 5)] + '-500']"
                        :style="'left: ' + (Math.random() * 100) + '%; top: ' + (Math.random() * 100) + '%; width: ' + (Math.random() * 10 + 5) + 'px; height: ' + (Math.random() * 10 + 5) + 'px; transform: rotate(' + (Math.random() * 360) + 'deg); animation: confetti-fall ' + (Math.random() * 3 + 2) + 's ease-in forwards;'"></div>
                </template>
            </div>

            <!-- Header -->
            <div class="px-8 py-6 bg-gradient-to-r from-green-50 to-blue-50 border-b border-gray-200/70">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 p-2 rounded-full bg-green-100 animate-pulse">
                            <i data-lucide="check-circle" class="h-8 w-8 text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900" id="thank-you-modal-title">Order Confirmed!</h3>
                            <p class="text-sm text-green-600 mt-1">Successfully sent to WhatsApp</p>
                        </div>
                    </div>
                    <button type="button" @click="isThankYouModalOpen = false"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none rounded-full p-1 transition-colors duration-200">
                        <span class="sr-only">Close</span>
                        <i data-lucide="x" class="h-6 w-6"></i>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="px-8 py-6 space-y-6">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100 mb-4">
                        <i data-lucide="circle-check-big" class="h-12 w-12 text-green-600 animate-bounce"></i>
                    </div>
                    <p class="text-lg text-gray-700">
                        We've sent your order details to WhatsApp. Please check your messages to complete payment.
                    </p>
                </div>

                <div x-show="finalOrderDetailsForThankYou && finalOrderDetailsForThankYou.orderRef"
                    class="p-5 bg-gradient-to-r from-blue-50 to-green-50 rounded-xl border border-blue-100 shadow-inner">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="hash" class="h-5 w-5 text-blue-500"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Your Order Reference:</p>
                            <p class="text-xl font-bold text-gray-900 tracking-wide" x-text="finalOrderDetailsForThankYou.orderRef"></p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50/50 p-4 rounded-lg border border-blue-100">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i data-lucide="info" class="h-5 w-5 text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">What's next?</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul role="list" class="list-disc pl-5 space-y-1">
                                    <li>Check your WhatsApp messages</li>
                                    <li>Confirm your order details</li>
                                    <li>Complete payment as instructed</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-4 bg-gray-50 border-t border-gray-200/70 flex items-right justify-end">
                <button type="button" @click="isThankYouModalOpen = false"
                    class="inline-flex items-center px-5 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>