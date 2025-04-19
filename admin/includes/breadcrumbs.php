<?php
// admin/includes/breadcrumbs.php

// Check if breadcrumbs array is set and not empty
if (isset($breadcrumbs) && is_array($breadcrumbs) && !empty($breadcrumbs)):
    // Ensure $breadcrumbs is numerically indexed for easy iteration count checks
    $breadcrumbs = array_values($breadcrumbs);
    $num_items = count($breadcrumbs);
?>
<nav aria-label="Breadcrumb" class="mb-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li>
            <a href="dashboard.php" title="Dashboard" class="hover:text-blue-600 hover:underline flex items-center">
                <i data-lucide="home" class="h-4 w-4 mr-1.5 flex-shrink-0"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
        </li>
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <?php 
                // Sanitize name and URL
                $crumb_name = isset($crumb['name']) ? htmlspecialchars($crumb['name']) : 'Unnamed';
                $crumb_url = isset($crumb['url']) ? htmlspecialchars($crumb['url']) : null;
                $is_last = ($index === $num_items - 1);
            ?>
            <li>
                <div class="flex items-center">
                    <i data-lucide="chevron-right" class="h-4 w-4 text-gray-400 flex-shrink-0"></i>
                    <?php if ($crumb_url && !$is_last): ?>
                        <a href="<?= $crumb_url ?>" class="ml-2 hover:text-blue-600 hover:underline">
                            <?= $crumb_name ?>
                        </a>
                    <?php else: // Last item or item without URL ?>
                        <span class="ml-2 font-medium text-gray-700" <?= $is_last ? 'aria-current="page"' : '' ?>>
                            <?= $crumb_name ?>
                        </span>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
<?php 
endif; 
// Optionally unset the variable if you are concerned about global scope pollution,
// though typically includes run in the scope of the calling file.
// unset($breadcrumbs);
?> 