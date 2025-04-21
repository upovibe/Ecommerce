<!-- Search Modal (Styled like Password Change Modal - Top Aligned) -->
<div id="searchModal" 
     class="fixed inset-0 z-50 overflow-y-auto hidden" 
     aria-hidden="true">
    
    <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
         <!-- Overlay -->
         <div id="searchModalOverlay"
             class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity ease-out duration-300 opacity-0"
             aria-hidden="true"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <!-- Modal Panel -->
        <div id="searchModalPanel"
             class="relative inline-block mx-auto p-6 md:p-8 border w-full max-w-xl shadow-2xl rounded-xl bg-white/95 backdrop-blur-xl transform transition-all ease-out duration-300 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             role="dialog" aria-modal="true" aria-labelledby="search-modal-title">
            
            <!-- Close Button -->
            <button id="closeSearchModalButton" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 transition-colors">
                <span class="sr-only">Close search</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>

            <div class="text-center mb-5">
                <h3 id="search-modal-title" class="text-xl md:text-2xl font-bold text-gray-900 mb-2">Search Products</h3>
                <div class="h-1 w-20 md:w-24 bg-gradient-to-r from-blue-500 to-blue-600 mx-auto rounded-full"></div>
            </div>

            <!-- Search Form -->
            <form action="/pages/products.php" method="GET">
                <label for="modalSearchInput" class="sr-only">Search</label>
                <div class="relative">
                     <!-- Search Icon inside input -->
                     <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           id="modalSearchInput" 
                           class="pl-10 pr-12 w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                           placeholder="What are you looking for?" 
                           required>
                    <!-- Submit Button inside input -->
                    <button type="submit" 
                            class="absolute inset-y-0 right-0 flex items-center justify-center px-4 text-blue-500 hover:text-blue-700 transition-colors rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-0">
                        <span class="sr-only">Search</span>
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lucide initialization script removed --> 