<div class="bg-white shadow-lg hover:shadow-xl transition-shadow duration-200 rounded-xl p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
        <i data-lucide="file-text" class="w-5 h-5 mr-2 text-blue-500"></i>
        Store Content
    </h2>
    <form id="contentForm" method="POST" @submit.prevent="saveStoreContent" class="space-y-6">
        
        <!-- Hero Section -->
        <fieldset class="space-y-4 border-t pt-4">
            <legend class="text-md font-medium text-gray-600 mb-2">Hero Section</legend>
            <div>
                <label for="hero_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="hero_title" id="hero_title" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    value="<?= htmlspecialchars($heroTitle) ?>">
            </div>
            <div>
                <label for="hero_subtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                <textarea name="hero_subtitle" id="hero_subtitle" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    ><?= htmlspecialchars($heroSubtitle) ?></textarea>
            </div>
        </fieldset>

        <!-- Hero Image Section -->
        <fieldset class="space-y-4 border-t pt-4">
            <legend class="text-md font-medium text-gray-600 mb-2">Hero Background Image</legend>
            <div>
                <label for="hero_image" class="block text-sm font-medium text-gray-700 mb-1">Upload New Image (Optional)</label>
                <input type="file" name="hero_image" id="hero_image" accept="image/jpeg, image/png, image/webp, image/gif"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                <p class="text-xs text-gray-500 mt-1">Recommended size: 1920x1080. Max 2MB. Formats: JPG, PNG, WEBP, GIF.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Image</label>
                <?php if (!empty($heroImage)): ?>
                    <img src="<?= htmlspecialchars($heroImage) ?>" alt="Current Hero Image" class="mt-2 rounded-lg border h-32 w-auto object-contain bg-gray-100">
                    <p class="text-xs text-gray-500 mt-1">Current path: <?= htmlspecialchars($heroImage) ?></p>
                <?php else: ?>
                    <p class="text-sm text-gray-500 mt-2">No hero image set.</p>
                <?php endif; ?>
            </div>
        </fieldset>

        <!-- Featured Section -->
        <fieldset class="space-y-4 border-t pt-4">
             <legend class="text-md font-medium text-gray-600 mb-2">Featured Products Section</legend>
             <div>
                <label for="featured_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="featured_title" id="featured_title" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    value="<?= htmlspecialchars($featuredTitle) ?>">
            </div>
            <div>
                <label for="featured_subtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                <textarea name="featured_subtitle" id="featured_subtitle" rows="2" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    ><?= htmlspecialchars($featuredSubtitle) ?></textarea>
            </div>
        </fieldset>

        <!-- About Section -->
        <fieldset class="space-y-4 border-t pt-4">
            <legend class="text-md font-medium text-gray-600 mb-2">About Section</legend>
            <div>
                <label for="about_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="about_title" id="about_title" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    value="<?= htmlspecialchars($aboutTitle) ?>">
            </div>
            <div>
                <label for="about_content" class="block text-sm font-medium text-gray-700 mb-1">Content (HTML allowed)</label>
                <textarea name="about_content" id="about_content" rows="6"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    ><?= htmlspecialchars($aboutContent) ?></textarea>
            </div>
        </fieldset>

        <!-- About Image Section -->
        <fieldset class="space-y-4 border-t pt-4">
            <legend class="text-md font-medium text-gray-600 mb-2">About Section Image</legend>
            <div>
                <label for="about_image" class="block text-sm font-medium text-gray-700 mb-1">Upload New Image (Optional)</label>
                <input type="file" name="about_image" id="about_image" accept="image/jpeg, image/png, image/webp, image/gif"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                <p class="text-xs text-gray-500 mt-1">Recommended aspect ratio: 4:3 or 16:9. Max 2MB. Formats: JPG, PNG, WEBP, GIF.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Image</label>
                <?php if (!empty($aboutImage)): ?>
                    <img src="<?= htmlspecialchars($aboutImage) ?>" alt="Current About Image" class="mt-2 rounded-lg border h-32 w-auto object-contain bg-gray-100">
                    <p class="text-xs text-gray-500 mt-1">Current path: <?= htmlspecialchars($aboutImage) ?></p>
                <?php else: ?>
                    <p class="text-sm text-gray-500 mt-2">No about image set.</p>
                <?php endif; ?>
            </div>
        </fieldset>

        <div class="pt-6 border-t border-gray-100 flex justify-end">
            <button type="submit" id="contentSubmitButton" :disabled="isSavingContent"
                    class="flex items-center px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" :class="{'hidden': !isSavingContent}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <i data-lucide="save" class="w-5 h-5 mr-2" :class="{'hidden': isSavingContent}"></i>
                <span x-text="isSavingContent ? 'Saving...' : 'Save Content'"></span>
            </button>
        </div>
    </form>
</div> 