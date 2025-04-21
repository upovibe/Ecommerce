<!-- Complete Order Modal -->
<?php require_once __DIR__ . '/../includes/country_codes.php'; ?>
<div id="completeOrderModal" 
     class="fixed inset-0 z-50 overflow-y-auto hidden" 
     aria-labelledby="complete-order-modal-title" role="dialog" aria-modal="true">
    
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div id="completeOrderModalOverlay"
             class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity ease-out duration-300 opacity-0"
             aria-hidden="true"></div>

        <!-- Centering span -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div id="completeOrderModalPanel"
             class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-xl text-left overflow-hidden shadow-2xl transform transition-all ease-out duration-300 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 sm:my-8 sm:align-middle sm:max-w-lg w-full">
            
            <!-- Header -->
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200/50">
                <h3 class="text-xl font-semibold text-gray-900" id="complete-order-modal-title">
                    Complete Your Order
                </h3>
                <button type="button" id="closeCompleteOrderModalButton" class="bg-white/50 rounded-lg text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2 backdrop-blur-xl">
                    <span class="sr-only">Close</span>
                    <!-- Replace Lucide icon with SVG -->
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto">
                 <form id="completeOrderForm" class="space-y-6" x-data>
                    
                    <!-- 1. Order Method (Button Radios) -->
                    <div class="pt-1 pb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">How will you like to get your order?</label>
                        <fieldset class="mt-2">
                            <legend class="sr-only">Order Method</legend>
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Delivery Option -->
                                <div>
                                    <input type="radio" name="deliveryMethod" value="delivery" id="deliveryMethodDelivery" checked class="sr-only peer">
                                    <label for="deliveryMethodDelivery" 
                                           class="flex items-center justify-center w-full p-3 border border-gray-300 rounded-lg cursor-pointer text-sm font-medium text-gray-700 bg-white transition duration-150 ease-in-out hover:bg-gray-50 
                                                  peer-checked:bg-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>]/10 
                                                  peer-checked:border-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] 
                                                  peer-checked:text-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] 
                                                  peer-checked:ring-2 
                                                  peer-checked:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>]">
                                         <!-- SVG for Delivery -->
                                         <i data-lucide="truck" class="h-5 w-5 mr-2 flex-shrink-0"></i>
                                        <span>Delivery</span>
                                    </label>
                                </div>
                                <!-- Pickup Option -->
                                <div>
                                    <input type="radio" name="deliveryMethod" value="pickup" id="deliveryMethodPickup" class="sr-only peer">
                                    <label for="deliveryMethodPickup" 
                                            class="flex items-center justify-center w-full p-3 border border-gray-300 rounded-lg cursor-pointer text-sm font-medium text-gray-700 bg-white transition duration-150 ease-in-out hover:bg-gray-50 
                                                   peer-checked:bg-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>]/10 
                                                   peer-checked:border-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] 
                                                   peer-checked:text-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] 
                                                   peer-checked:ring-2 
                                                   peer-checked:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>]">
                                        <!-- SVG for Pickup -->
                                        <i data-lucide="shopping-bag" class="h-5 w-5 mr-2 flex-shrink-0"></i>
                                        <span>Pickup</span>
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    
                    <!-- 2. Recipient (Button Radios) -->
                    <div class="py-3 border-t border-gray-200/70">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Who will receive this item?</label>
                        <fieldset class="mt-2">
                            <legend class="sr-only">Recipient Type</legend>
                             <div class="grid grid-cols-2 gap-3">
                                <!-- Myself Option -->
                                <div>
                                    <input type="radio" name="recipientType" value="myself" id="recipientTypeMyself" checked class="sr-only peer">
                                    <label for="recipientTypeMyself" 
                                           class="flex items-center justify-center w-full p-3 border border-gray-300 rounded-lg cursor-pointer text-sm font-medium text-gray-700 bg-white transition duration-150 ease-in-out hover:bg-gray-50 
                                                  peer-checked:bg-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>]/10 
                                                  peer-checked:border-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] 
                                                  peer-checked:text-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] 
                                                  peer-checked:ring-2 
                                                  peer-checked:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>]">
                                         <!-- SVG for Myself -->
                                         <i data-lucide="user" class="h-5 w-5 mr-2 flex-shrink-0"></i>
                                        <span>Myself</span>
                                    </label>
                                </div>
                                <!-- Someone Else Option -->
                                <div>
                                     <input type="radio" name="recipientType" value="someoneElse" id="recipientTypeSomeoneElse" class="sr-only peer">
                                    <label for="recipientTypeSomeoneElse" 
                                           class="flex items-center justify-center w-full p-3 border border-gray-300 rounded-lg cursor-pointer text-sm font-medium text-gray-700 bg-white transition duration-150 ease-in-out hover:bg-gray-50 
                                                  peer-checked:bg-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>]/10 
                                                  peer-checked:border-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] 
                                                  peer-checked:text-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] 
                                                  peer-checked:ring-2 
                                                  peer-checked:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>]">
                                         <!-- SVG for Someone Else -->
                                         <i data-lucide="gift" class="h-5 w-5 mr-2 flex-shrink-0"></i>
                                        <span>Someone Else</span>
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    
                     <!-- === Accordion Wrapper === -->
                     <div class="border-t border-gray-200/70 pt-2 space-y-0 divide-y divide-gray-200/70">

                        <!-- === Your/Sender Details Accordion === -->
                        <div class="py-2" x-data="{ open: true }">
                            <button type="button" @click="open = !open" class="flex justify-between items-center w-full text-left py-3 px-1 hover:bg-gray-50/50 rounded-md transition-colors duration-150">
                                <!-- Title changes based on selection -->
                                <span id="primaryDetailsTitle" class="text-base font-semibold text-gray-800">Your Details</span>
                                <i data-lucide="chevron-down" class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" x-collapse.duration.300ms class="mt-2 space-y-4 pt-2 pb-3 px-1 transition-all duration-300 ease-in-out overflow-hidden">
                                <!-- Fields for Myself (Show when Recipient = Myself) -->
                                <div id="myselfFields" class="space-y-4">
                                    <!-- Name Input with Icon -->
                                    <div>
                                        <label for="customerFullName" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                                        <div class="mt-1 relative rounded-lg shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i data-lucide="user" class="h-5 w-5 text-gray-400"></i>
                                            </div>
                                            <input type="text" name="customerFullName" id="customerFullName" autocomplete="name" required 
                                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                        </div>
                                    </div>
                                     <!-- WhatsApp Input with Icon -->
                                    <div>
                                        <label for="customerWhatsapp_number" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number <span class="text-red-500">*</span></label>
                                        <div class="mt-1 flex rounded-lg shadow-sm">
                                            <input type="hidden" name="customerWhatsapp_code" id="customerWhatsapp_code" value="<?= $defaultCountryCode ?>">
                                            <select id="customerWhatsapp_select" 
                                                    class="block pl-3 pr-2 py-2 border border-r-0 border-gray-300 rounded-l-lg bg-gray-100 text-gray-700 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm appearance-none text-center" 
                                                    style="max-width: 6rem;">
                                                <?php foreach ($countries as $country): ?>
                                                    <option value="<?= htmlspecialchars($country['code']) ?>" <?= ($country['code'] == $defaultCountryCode) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($country['flag']) ?> <?= htmlspecialchars($country['code']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="tel" name="customerWhatsapp_number" id="customerWhatsapp_number" autocomplete="tel" required 
                                                   placeholder="Enter number"
                                                   class="flex-1 block w-full px-3 py-2 border border-l-0 border-gray-300 rounded-r-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                        </div>
                                    </div>
                                     <!-- Email Input with Icon -->
                                    <div>
                                        <label for="customerEmail" class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                                        <div class="mt-1 relative rounded-lg shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                 <i data-lucide="mail" class="h-5 w-5 text-gray-400"></i>
                                            </div>
                                            <input type="email" name="customerEmail" id="customerEmail" autocomplete="email" required 
                                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                        </div>
                                    </div>
                                    <!-- Delivery Address for Myself (Conditionally shown) -->
                                    <div id="customerDeliveryAddressContainer" class="space-y-3" x-data="{ show: document.querySelector('input[name=deliveryMethod]:checked').value === 'delivery' }" x-show="show" x-init="$watch(() => document.querySelector('input[name=deliveryMethod]:checked').value, value => show = value === 'delivery')" x-collapse>
                                        <label for="customerAddress" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address <span class="text-red-500">*</span></label>
                                         <div class="mt-1 relative rounded-lg shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i data-lucide="map-pin" class="h-5 w-5 text-gray-400"></i>
                                            </div>
                                            <textarea id="customerAddress" name="customerAddress" rows="3" :required="show" 
                                                      class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fields for Sender (Show when Recipient = Someone Else) -->
                                <div id="senderFieldsContainer" class="hidden space-y-4">
                                    <!-- Name, Whatsapp, Email -->
                                     <div>
                                        <label for="senderFullName" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                                         <div class="mt-1 relative rounded-lg shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i data-lucide="user" class="h-5 w-5 text-gray-400"></i>
                                            </div>
                                            <input type="text" name="senderFullName" id="senderFullName" autocomplete="name" required 
                                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="senderWhatsapp_number" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number <span class="text-red-500">*</span></label>
                                        <div class="mt-1 flex rounded-lg shadow-sm">
                                            <input type="hidden" name="senderWhatsapp_code" id="senderWhatsapp_code" value="<?= $defaultCountryCode ?>">
                                            <select id="senderWhatsapp_select"
                                                    class="block pl-3 pr-2 py-2 border border-r-0 border-gray-300 rounded-l-lg bg-gray-100 text-gray-700 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm appearance-none text-center"
                                                    style="max-width: 6rem;">
                                                <?php foreach ($countries as $country): ?>
                                                    <option value="<?= htmlspecialchars($country['code']) ?>" <?= ($country['code'] == $defaultCountryCode) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($country['flag']) ?> <?= htmlspecialchars($country['code']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="tel" name="senderWhatsapp_number" id="senderWhatsapp_number" autocomplete="tel" required
                                                   placeholder="Enter number"
                                                   class="flex-1 block w-full px-3 py-2 border border-l-0 border-gray-300 rounded-r-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                        </div>
                                    </div>
                                     <div>
                                        <label for="senderEmail" class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                                        <div class="mt-1 relative rounded-lg shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i data-lucide="mail" class="h-5 w-5 text-gray-400"></i>
                                            </div>
                                            <input type="email" name="senderEmail" id="senderEmail" autocomplete="email" required 
                                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- === Recipient Details Accordion (Show when Recipient = Someone Else) === -->
                        <div id="recipientAccordionContainer" class="hidden py-2" x-data="{ open: true }">
                            <button type="button" @click="open = !open" class="flex justify-between items-center w-full text-left py-3 px-1 hover:bg-gray-50/50 rounded-md transition-colors duration-150">
                                <span class="text-base font-semibold text-gray-800">Recipient's Details</span>
                                <i data-lucide="chevron-down" class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" x-collapse.duration.300ms class="mt-2 space-y-4 pt-2 pb-3 px-1 transition-all duration-300 ease-in-out overflow-hidden">
                                <div>
                                    <label for="receiverFullName" class="block text-sm font-medium text-gray-700 mb-1">Recipient's Full Name <span class="text-red-500">*</span></label>
                                    <div class="mt-1 relative rounded-lg shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="user" class="h-5 w-5 text-gray-400"></i>
                                        </div>
                                        <input type="text" name="receiverFullName" id="receiverFullName" required 
                                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label for="receiverWhatsapp_number" class="block text-sm font-medium text-gray-700 mb-1">Recipient's WhatsApp Number <span class="text-red-500">*</span></label>
                                    <div class="mt-1 flex rounded-lg shadow-sm">
                                        <input type="hidden" name="receiverWhatsapp_code" id="receiverWhatsapp_code" value="<?= $defaultCountryCode ?>">
                                        <select id="receiverWhatsapp_select"
                                                class="block pl-3 pr-2 py-2 border border-r-0 border-gray-300 rounded-l-lg bg-gray-100 text-gray-700 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm appearance-none text-center"
                                                style="max-width: 6rem;">
                                            <?php foreach ($countries as $country): ?>
                                                <option value="<?= htmlspecialchars($country['code']) ?>" <?= ($country['code'] == $defaultCountryCode) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($country['flag']) ?> <?= htmlspecialchars($country['code']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="tel" name="receiverWhatsapp_number" id="receiverWhatsapp_number" required
                                               placeholder="Enter number"
                                               class="flex-1 block w-full px-3 py-2 border border-l-0 border-gray-300 rounded-r-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                    </div>
                                </div>
                                <!-- Delivery Address for Recipient (Conditionally shown) -->
                                <div id="receiverDeliveryAddressContainer" class="space-y-3" x-data="{ show: document.querySelector('input[name=deliveryMethod]:checked').value === 'delivery' }" x-show="show" x-init="$watch(() => document.querySelector('input[name=deliveryMethod]:checked').value, value => show = value === 'delivery')" x-collapse>
                                    <label for="receiverAddress" class="block text-sm font-medium text-gray-700 mb-1">Recipient's Delivery Address <span class="text-red-500">*</span></label>
                                     <div class="mt-1 relative rounded-lg shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="map-pin" class="h-5 w-5 text-gray-400"></i>
                                        </div>
                                        <textarea id="receiverAddress" name="receiverAddress" rows="3" :required="show" 
                                                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- === Notes & Coupon Accordion === -->
                        <div class="py-2" x-data="{ open: false }">
                             <button type="button" @click="open = !open" class="flex justify-between items-center w-full text-left py-3 px-1 hover:bg-gray-50/50 rounded-md transition-colors duration-150">
                                <span class="text-base font-semibold text-gray-800">Notes & Coupon (Optional)</span>
                                <i data-lucide="chevron-down" class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" x-collapse.duration.300ms class="mt-2 space-y-4 pt-2 pb-3 px-1 transition-all duration-300 ease-in-out overflow-hidden">
                                <div>
                                    <label for="orderNotes" class="block text-sm font-medium text-gray-700 mb-1">Order Notes</label>
                                    <textarea id="orderNotes" name="orderNotes" rows="2" class="mt-1 block w-full border border-gray-300 rounded-lg bg-gray-50 shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm"></textarea>
                                </div>
                                <div>
                                    <label for="couponCode" class="block text-sm font-medium text-gray-700 mb-1">Coupon Code</label>
                                     <div class="mt-1 relative rounded-lg shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="tag" class="h-5 w-5 text-gray-400"></i>
                                        </div>
                                        <input type="text" name="couponCode" id="couponCode" 
                                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                 </form>
            </div>

            <!-- Footer (optional, can add actions later) -->
            <div class="bg-gray-50/70 px-4 py-3 sm:px-6 flex justify-end items-center gap-3 border-t border-gray-200/50">
                 <button type="button" id="cancelCompleteOrderButton" 
                         class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:w-auto sm:text-sm">
                    Cancel
                </button>
                 <button type="submit" form="completeOrderForm" id="confirmOrderButton" 
                         class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white 
                                bg-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] hover:brightness-90 
                                focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[<?= htmlspecialchars($themeColor ?? '#3B82F6') ?>] 
                                sm:w-auto sm:text-sm disabled:opacity-50 min-w-[130px] transition duration-150 ease-in-out">
                    <!-- Add disabled state handling later if needed -->
                     Confirm Order
                </button>
            </div>
        </div>
    </div>
</div> 