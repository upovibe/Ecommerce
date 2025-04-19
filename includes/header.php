<?php
require_once __DIR__ . '/../config/settings.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= STORE_SETTINGS['store_name'] ?? 'E-Commerce Store' ?></title>
    <meta name="description" content="<?= STORE_SETTINGS['store_description'] ?? 'Your one-stop shop for all your needs' ?>">
    <script src="https://cdn.tailwindcss.com/"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "<?= STORE_SETTINGS['theme_color'] ?? '#3B82F6' ?>",
                    }
                }
            }
        }
    </script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/animations.css">
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <div class="flex-grow">
        <!-- Header content will appear here -->
    </div>
</body>
</html>