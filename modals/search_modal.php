<!-- Search Modal -->
<div x-show="isSearchModalOpen" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="search-modal-title" role="dialog" aria-modal="true"
     @keydown.escape.window="isSearchModalOpen = false">

    <div class="flex items-start justify-center min-h-screen pt-10 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="isSearchModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity"
             @click="isSearchModalOpen = false" aria-hidden="true"></div>

        <!-- Align vertical trick -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div x-show="isSearchModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-transparent text-left overflow-visible transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

            <!-- Simplified Search Input Section -->
            <div class="relative mt-1 rounded-lg shadow-lg">
                <input type="search" name="search" id="modal-search-input"
                       placeholder="Search products..."
                       class="block w-full max-w-screen-lg pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                <button type="button" 
                        id="modal-search-trigger"
                        title="Search"
                        class="absolute inset-y-0 right-0 flex items-center justify-center px-3 text-gray-400 hover:text-blue-600">
                    <i data-lucide="search" class="h-5 w-5"></i>
                </button>
            </div>
            <!-- NOTE: Results would likely need to appear elsewhere or redirect the page -->
            
        </div>
    </div>
</div>
<!-- End Search Modal --> 