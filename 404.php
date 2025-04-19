<?php
require_once __DIR__ . '/config/settings.php'; 
$storeName = STORE_SETTINGS['store_name'] ?? 'E-Commerce Store';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - <?= htmlspecialchars($storeName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
        }
        
        .error-container {
            position: relative;
            overflow: hidden;
        }
        
        .error-container::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 50%;
            z-index: 0;
        }
        
        .error-container::after {
            content: "";
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 300px;
            height: 300px;
            background: rgba(59, 130, 246, 0.05);
            border-radius: 50%;
            z-index: 0;
        }
        
        .home-button {
            transition: all 0.3s ease;
            transform: translateY(0);
        }
        
        .home-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.3);
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="error-container bg-white rounded-2xl shadow-xl p-8 md:p-12 max-w-md w-full relative z-10 pulse">
        <div class="flex justify-center mb-6">
            <div class="bg-red-50 p-4 rounded-full">
                <i data-lucide="alert-octagon" class="w-12 h-12 text-red-500"></i>
            </div>
        </div>
        
        <h1 class="text-8xl font-extrabold text-gray-800 mb-2 bg-gradient-to-r from-blue-600 to-red-500 bg-clip-text text-transparent">404</h1>
        <h2 class="text-2xl font-bold text-gray-800 mb-3">Lost in the digital void</h2>
        <p class="text-gray-500 mb-8">The page you're looking for has either moved, been deleted, or never existed.</p>
        
        <div class="flex flex-col space-y-4">
            <a href="index.php" 
               class="home-button inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white font-medium rounded-lg shadow-md">
                <i data-lucide="home" class="w-5 h-5 mr-2"></i>
                Return to Homepage
            </a>
    </div>

    <script>
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        // Add micro-interaction for the error container
        document.querySelector('.error-container').addEventListener('mouseenter', function() {
            this.classList.remove('pulse');
        });
        
        document.querySelector('.error-container').addEventListener('mouseleave', function() {
            this.classList.add('pulse');
        });
    </script>
</body>
</html>