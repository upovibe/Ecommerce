<!-- Settings Form -->
<div>
    <div class="bg-white shadow-lg hover:shadow-xl transition-shadow duration-200 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
            <i data-lucide="settings" class="w-5 h-5 mr-2 text-blue-500"></i>
            General Settings
        </h2>
        <form id="settingsForm" method="POST" @submit.prevent="saveGeneralSettings" class="space-y-6">
            
            <fieldset class="space-y-4">
                <legend class="text-md font-medium text-gray-600 mb-2">Store Information</legend>
                <div>
                    <label for="store_name" class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="shopping-bag" class="w-5 h-5 text-blue-500"></i>
                        </div>
                        <input type="text" name="store_name" id="store_name" required
                            class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            value="<?= htmlspecialchars($storeName) ?>">
                    </div>
                </div>
                
                <div>
                    <label for="store_description" class="block text-sm font-medium text-gray-700 mb-1">Store Description</label>
                    <textarea name="store_description" id="store_description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        ><?= htmlspecialchars($storeDescription) ?></textarea>
                </div>
            </fieldset>

            <fieldset class="space-y-4 border-t pt-4">
                <legend class="text-md font-medium text-gray-600 mb-2">Contact & Currency</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="message-circle" class="w-5 h-5 text-blue-500"></i>
                            </div>
                            <input type="text" name="whatsapp_number" id="whatsapp_number"
                                class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                value="<?= htmlspecialchars($whatsappNumber) ?>">                                  
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500">Format: country code + number (e.g., 2348012345678)</p>
                    </div>

                    <div>
                        <label for="currency_symbol" class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="landmark" class="w-5 h-5 text-blue-500"></i>
                            </div>
                            <input type="text" name="currency_symbol" id="currency_symbol"
                                class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                value="<?= htmlspecialchars($currencySymbol) ?>">
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-4 border-t pt-4">
                 <legend class="text-md font-medium text-gray-600 mb-2">Messaging & Footer</legend>
                <div>
                    <label for="whatsapp_message_template" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Message Template</label>
                    <div class="relative">
                        <div class="absolute top-3 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="message-square" class="w-5 h-5 text-blue-500"></i>
                        </div>
                        <textarea name="whatsapp_message_template" id="whatsapp_message_template" rows="4"
                            class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            ><?= htmlspecialchars($whatsappTemplate) ?></textarea>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">Placeholders: {ITEMS}, {TOTAL}, {CURRENCY}</p>
                </div>
                <div>
                    <label for="footer_text" class="block text-sm font-medium text-gray-700 mb-1">Footer Text</label>
                    <div class="relative">
                        <div class="absolute top-3 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="pilcrow" class="w-5 h-5 text-blue-500"></i>
                        </div>
                        <textarea name="footer_text" id="footer_text" rows="4"
                            class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            ><?= htmlspecialchars($footerText) ?></textarea>
                    </div>
                </div>
            </fieldset>

            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" id="settingsSubmitButton" :disabled="isSavingGeneral" 
                        class="flex items-center px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" :class="{'hidden': !isSavingGeneral}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <i data-lucide="save" class="w-5 h-5 mr-2" :class="{'hidden': isSavingGeneral}"></i>
                    <span x-text="isSavingGeneral ? 'Saving...' : 'Save Changes'"></span>
                </button>
            </div>
        </form>
    </div>
</div> 