function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('[data-lucide]');
    const isVisible = icon.getAttribute('data-visible') === 'true';
    
    // Toggle password visibility
    input.type = isVisible ? 'password' : 'text';
    icon.setAttribute('data-visible', !isVisible);
    
    // Update icon
    icon.setAttribute('data-lucide', isVisible ? 'eye' : 'eye-off');
    
    // Update Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    } else {
        console.warn('Lucide library not found when trying to update icons.');
    }
}

// Handle password change form submission
// Ensure the toast object is available globally or initialized before this script runs
if (document.getElementById('passwordChangeForm')) {
    document.getElementById('passwordChangeForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = this;
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonContent = submitButton.innerHTML;
        const newPasswordInput = form.querySelector('#new_password');
        const confirmPasswordInput = form.querySelector('#confirm_password');
        const currentPasswordInput = form.querySelector('#current_password'); // Added current password input for error highlighting
        const newPasswordError = form.querySelector('#new-password-error');
        const confirmPasswordError = form.querySelector('#confirm-password-error');

        // Ensure toast object exists
        if (typeof toast === 'undefined') {
            console.error('Toast notification system not found.');
            // Optionally, use a simple alert as fallback
            alert('Error: Notification system unavailable.'); 
            return;
        }

        // Clear previous errors
        newPasswordError.textContent = '';
        newPasswordError.classList.add('hidden');
        newPasswordInput.classList.remove('border-red-500', 'focus:ring-red-500');
        confirmPasswordError.textContent = '';
        confirmPasswordError.classList.add('hidden');
        confirmPasswordInput.classList.remove('border-red-500', 'focus:ring-red-500');
        currentPasswordInput.classList.remove('border-red-500', 'focus:ring-red-500'); // Clear current password error styling
        
        const currentPassword = currentPasswordInput.value; // Get value
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        let hasError = false;

        // --- Client-side Validation --- 
        if (newPassword.length < 6) {
            newPasswordError.textContent = 'Password must be at least 6 characters long.';
            newPasswordError.classList.remove('hidden');
            newPasswordInput.classList.add('border-red-500', 'focus:ring-red-500');
            hasError = true;
        }

        if (newPassword !== confirmPassword) {
            confirmPasswordError.textContent = 'Passwords do not match.';
            confirmPasswordError.classList.remove('hidden');
            confirmPasswordInput.classList.add('border-red-500', 'focus:ring-red-500');
            // Also mark the new password field if length is okay but they don't match
            if (!hasError) { // Avoid double-marking if length was already wrong
                 newPasswordInput.classList.add('border-red-500', 'focus:ring-red-500');
            }
            hasError = true;
        }

        if (hasError) {
            toast.error('Please fix the errors in the form.');
            submitButton.disabled = false; // Re-enable button
            submitButton.innerHTML = originalButtonContent; // Restore button text
            // Recreate icon if needed after restoring HTML
            if (typeof lucide !== 'undefined') lucide.createIcons(); 
            return; // Stop submission
        }
        // --- End Client-side Validation ---
        
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Updating...
        `;
        
        try {
            // Collect data manually to send as JSON
            const body = {
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            };
            
            const response = await fetch('utils/update_password.php', {
                method: 'POST',
                headers: {
                     'Content-Type': 'application/json', // Specify JSON content type
                     'Accept': 'application/json'
                },
                body: JSON.stringify(body) // Send JSON string
            });
            
            const data = await response.json();
            
            if (data.success) {
                toast.success(data.message + ' Logging you out...');
                // Hide the modal immediately
                const modal = document.getElementById('changePasswordModal');
                if (modal) {
                    modal.classList.add('hidden');
                }
                // Redirect to logout after a delay
                setTimeout(() => {
                    window.location.href = 'logout.php';
                }, 3000); // 3-second delay before logout
            } else {
                toast.error(data.message);
                // Add red border to current password field if backend indicates it's wrong
                if (data.message && data.message.toLowerCase().includes('incorrect current password')) {
                    currentPasswordInput.classList.add('border-red-500', 'focus:ring-red-500');
                }
                // Reset the form on error
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonContent;
                // Recreate icon if needed after restoring HTML
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        } catch (error) {
            console.error('Error:', error);
            toast.error('An error occurred while updating the password');
            // Reset the button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonContent;
             // Recreate icon if needed after restoring HTML
             if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    });
}

// Initialize Lucide icons if they exist on the page where this script is loaded
// This might be redundant if Lucide is already initialized elsewhere, but safe to include
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}); 