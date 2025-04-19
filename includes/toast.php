<?php
// Toast container that will be populated by JavaScript
?>
<div id="toastContainer" class="fixed top-4 right-4 z-50 flex flex-col gap-4"></div>

<script>
const toast = {
    show: function(message, type = 'success', duration = 3000) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        
        // Set base classes
        toast.className = `transform transition-all duration-300 ease-out translate-x-full
                          flex items-center p-4 mb-4 rounded-lg shadow-lg max-w-sm`;
        
        // Add type-specific classes and icon
        let icon = '';
        switch(type) {
            case 'success':
                toast.classList.add('bg-green-100', 'border-l-4', 'border-green-500', 'text-green-700');
                icon = '<i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>';
                break;
            case 'error':
                toast.classList.add('bg-red-100', 'border-l-4', 'border-red-500', 'text-red-700');
                icon = '<i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i>';
                break;
            case 'warning':
                toast.classList.add('bg-amber-100', 'border-l-4', 'border-amber-500', 'text-amber-700');
                icon = '<i data-lucide="alert-triangle" class="w-5 h-5 mr-2"></i>';
                break;
        }
        
        // Set content
        toast.innerHTML = `
            <div class="flex items-center">
                ${icon}
                <p class="text-sm font-medium">${message}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="ml-auto pl-3 hover:text-gray-900">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        `;
        
        // Add to container
        container.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }
            }, duration);
        }
    },
    
    success: function(message, duration) {
        this.show(message, 'success', duration);
    },
    
    error: function(message, duration) {
        this.show(message, 'error', duration);
    },
    
    warning: function(message, duration) {
        this.show(message, 'warning', duration);
    }
};
</script>