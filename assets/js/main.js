/**
 * Main JavaScript for E-Commerce Template
 */

// --- Homepage Category/Subcategory Logic ---

async function fetchSubcategories(event, element) {
    event.preventDefault(); // Prevent default link behavior
    const categoryId = element.dataset.categoryId;
    const categoryName = element.dataset.categoryName;
    const subcategoryContent = element.querySelector('.category-subcategory-content');
    const subcategoryList = subcategoryContent.querySelector('.subcategory-list');
    const parentContent = element.querySelector('.category-parent-content');
    const loadingText = subcategoryList.querySelector('.loading-text');

    // Ensure elements exist before proceeding
    if (!subcategoryContent || !subcategoryList || !parentContent) {
        console.error('Required elements not found within category card.');
        return;
    }

    // Show loading state
    parentContent.style.display = 'none';
    subcategoryList.innerHTML = '<p class="text-center text-gray-400 italic loading-text">Loading...</p>'; // Reset list
    subcategoryContent.style.display = 'block';
    
    // Hide other open subcategory lists (optional)
    document.querySelectorAll('.category-subcategory-content').forEach(el => {
        if(el !== subcategoryContent) {
             el.style.display = 'none';
        }
    });
     document.querySelectorAll('.category-parent-content').forEach(el => {
        if(el !== parentContent) {
             el.style.display = 'block'; // Ensure other parent contents are visible
        }
    });

    try {
        const response = await fetch(`./utils/get_subcategories.php?parent_id=${categoryId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();

        if (data.success && data.subcategories.length > 0) {
            subcategoryList.innerHTML = ''; // Clear loading/previous list
             // Limit to 8 subcategories
             const subcategoriesToShow = data.subcategories.slice(0, 8);
            subcategoriesToShow.forEach(sub => {
                const subItem = document.createElement('a');
                subItem.href = `/pages/products.php?category=${sub.id}`; // Link to products page for subcategory
                subItem.className = 'block px-2 py-1 hover:bg-gray-100 rounded';
                subItem.textContent = sub.name;
                subcategoryList.appendChild(subItem);
            });
        } else if (data.success && data.subcategories.length === 0) {
            subcategoryList.innerHTML = '<p class="text-center text-gray-500">No subcategories found.</p>';
        } else {
            subcategoryList.innerHTML = '<p class="text-center text-red-500">Error loading subcategories.</p>';
            console.error('API error:', data.message);
        }
    } catch (error) {
        console.error('Fetch error:', error);
        subcategoryList.innerHTML = '<p class="text-center text-red-500">Could not fetch subcategories.</p>';
    }
}

function showParentCategory(event, buttonElement) {
    event.stopPropagation(); // Prevent event from bubbling up to the main card click
    event.preventDefault();
    const card = buttonElement.closest('.category-card');
    if (!card) return; // Exit if card not found
    
    const subcategoryContent = card.querySelector('.category-subcategory-content');
    const parentContent = card.querySelector('.category-parent-content');
    
    if (subcategoryContent) subcategoryContent.style.display = 'none';
    if (parentContent) parentContent.style.display = 'block';
}

// --- Search Modal Logic --- 
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('modal-search-input');
    const searchTrigger = document.getElementById('modal-search-trigger');

    // Check if the elements exist on the current page
    if (!searchInput || !searchTrigger) {
        // console.log('Search modal elements not found on this page.');
        return; // Exit if modal elements aren't present
    }

    function performSearch() {
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            // Redirect to products page with search query
            window.location.href = `/products.php?search=${encodeURIComponent(searchTerm)}`;
        } else {
            // Optional: Provide feedback if search term is empty
            // console.log('Search term is empty.');
             searchInput.focus(); // Focus input if empty search attempted
        }
    }

    // Trigger search on button click
    searchTrigger.addEventListener('click', performSearch);

    // Trigger search on Enter key press in input
    searchInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent default form submission (if any)
            performSearch();
        }
    });
});
