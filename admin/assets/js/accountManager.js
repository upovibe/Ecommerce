function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = input.closest('.relative').querySelector(`button[onclick="togglePasswordVisibility('${inputId}')"]`); 
    if (!button) return;
    const icon = button.querySelector('[data-lucide]');
    if (!icon) return;

    const isVisible = icon.getAttribute('data-visible') === 'true';
    
    // Toggle password visibility
    input.type = isVisible ? 'password' : 'text';
    icon.setAttribute('data-visible', !isVisible);
    
    // Update icon
    icon.setAttribute('data-lucide', isVisible ? 'eye' : 'eye-off');
    
    // Update Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Function to handle profile update form submission
async function updateProfile(event) {
    // Find the Alpine component data context associated with the form
    const alpineData = Alpine.closestDataStack(event.target)[0]; 
    if (!alpineData) {
        console.error('Alpine data context not found for profile form.');
        toast.error('Could not process profile update. Please refresh.');
        return;
    }
    alpineData.profileLoading = true;
    
    const body = {
        full_name: alpineData.profile.full_name,
        gender: alpineData.profile.gender,
        about: alpineData.profile.about
    };

    try {
        const response = await fetch('utils/update_profile.php', { 
            method: 'POST',
            headers: {
                 'Content-Type': 'application/json',
                 'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        });
        
        const data = await response.json();
        
        if (data.success) {
            toast.success(data.message || 'Profile updated successfully!');
        } else {
            toast.error(data.message || 'Failed to update profile.');
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        toast.error('An error occurred while updating profile.');
    } finally {
        if (alpineData) { // Check again in case it somehow became undefined
            alpineData.profileLoading = false;
        }
    }
}

// Handle password change form submission
document.getElementById('passwordChangeForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonHTML = submitButton.innerHTML; // Store full HTML
    const newPasswordInput = form.querySelector('#account_new_password');
    const confirmPasswordInput = form.querySelector('#account_confirm_password');
    const currentPasswordInput = form.querySelector('#account_current_password'); // Need current pass input too
    const newPasswordError = form.querySelector('#account-new-password-error');
    const confirmPasswordError = form.querySelector('#account-confirm-password-error');

    // Clear previous errors
    newPasswordError.textContent = '';
    newPasswordError.classList.add('hidden');
    newPasswordInput.classList.remove('border-red-500', 'focus:ring-red-500');
    confirmPasswordError.textContent = '';
    confirmPasswordError.classList.add('hidden');
    confirmPasswordInput.classList.remove('border-red-500', 'focus:ring-red-500');
    currentPasswordInput.classList.remove('border-red-500', 'focus:ring-red-500'); // Also clear current pass error styling
    
    const newPassword = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    const currentPassword = currentPasswordInput.value; // Get current password value

    let hasError = false;
    let success = false; // Flag to track success for the finally block

    // --- Client-side Validation --- 
     if (!currentPassword) { // Basic check for current password
        // Optionally add inline error for current password too
        currentPasswordInput.classList.add('border-red-500', 'focus:ring-red-500');
        hasError = true;
    }

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
        if (newPassword.length >= 6) { 
             newPasswordInput.classList.add('border-red-500', 'focus:ring-red-500');
        }
        hasError = true;
    }

    if (hasError) {
        toast.error('Please fix the errors in the form.');
        submitButton.disabled = false; // Re-enable button
        submitButton.innerHTML = originalButtonHTML; // Restore button HTML
        if (typeof lucide !== 'undefined') lucide.createIcons(); // Recreate icon if needed
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
        const formData = new FormData(form);
        // Ensure correct data is sent (matching update_password.php expectations)
        const body = {
             current_password: currentPassword,
             new_password: newPassword,
             confirm_password: confirmPassword // Send confirm pass too for backend check if needed
        };

        const response = await fetch('utils/update_password.php', { // Point to the correct backend script
            method: 'POST',
            headers: {
                 'Content-Type': 'application/json', // Send as JSON
                 'Accept': 'application/json'
            },
            body: JSON.stringify(body) // Stringify the JS object
        });
        
        const data = await response.json();
        
        if (data.success) {
            success = true; // Set flag on success
            toast.success(data.message || 'Password updated successfully! You will be logged out shortly...');
            // Clear the form fields on success
            form.reset(); 
            
            // REMOVED setTimeout from here
        } else {
            toast.error(data.message || 'Failed to update password. Check current password?');
            // Add red border to current password field if backend indicates it's wrong
            if (data.message && data.message.toLowerCase().includes('incorrect current password')) {
                 currentPasswordInput.classList.add('border-red-500', 'focus:ring-red-500');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        toast.error('An error occurred while updating the password');
    } finally {
         // Reset the button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonHTML;
         if (typeof lucide !== 'undefined') lucide.createIcons(); // Recreate icon

         // Trigger logout redirect only if successful
         if (success) {
             setTimeout(() => {
                 window.location.href = 'logout.php';
             }, 3000);
         }
    }
});

// Handle username change form submission
document.getElementById('usernameChangeForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonHTML = submitButton.innerHTML;
    const newUsernameInput = form.querySelector('#account_new_username');
    const currentPasswordInput = form.querySelector('#account_confirm_password_for_username');
    const newUsernameError = form.querySelector('#account-new-username-error');
    const currentPasswordError = form.querySelector('#account-confirm-password-username-error');

    // Clear previous errors
    newUsernameError.textContent = '';
    newUsernameError.classList.add('hidden');
    newUsernameInput.classList.remove('border-red-500', 'focus:ring-red-500');
    currentPasswordError.textContent = '';
    currentPasswordError.classList.add('hidden');
    currentPasswordInput.classList.remove('border-red-500', 'focus:ring-red-500');

    const newUsername = newUsernameInput.value.trim();
    const currentPassword = currentPasswordInput.value;

    let hasError = false;
    let success = false;

    // Basic Client-side Validation
    if (!newUsername) {
        newUsernameError.textContent = 'New username cannot be empty.';
        newUsernameError.classList.remove('hidden');
        newUsernameInput.classList.add('border-red-500', 'focus:ring-red-500');
        hasError = true;
    }
    if (!currentPassword) {
        currentPasswordError.textContent = 'Current password is required.';
        currentPasswordError.classList.remove('hidden');
        currentPasswordInput.classList.add('border-red-500', 'focus:ring-red-500');
        hasError = true;
    }

    if (hasError) {
        toast.error('Please fix the errors in the form.');
        return; // Stop submission
    }

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
        const body = {
            new_username: newUsername,
            current_password: currentPassword
        };

        const response = await fetch('utils/update_username.php', {
            method: 'POST',
            headers: {
                 'Content-Type': 'application/json',
                 'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        });

        const data = await response.json();

        if (data.success) {
            success = true;
            toast.success(data.message || 'Username updated successfully! Logging out...');
            form.reset(); 
        } else {
            toast.error(data.message || 'Failed to update username.');
            // Highlight relevant field based on error message
            if (data.message && data.message.toLowerCase().includes('incorrect current password')) {
                 currentPasswordInput.classList.add('border-red-500', 'focus:ring-red-500');
                 currentPasswordError.textContent = 'Incorrect current password.';
            }
            if (data.message && data.message.toLowerCase().includes('username is already taken')) {
                 newUsernameInput.classList.add('border-red-500', 'focus:ring-red-500');
                 newUsernameError.textContent = 'This username is already taken.';
                 newUsernameError.classList.remove('hidden');
            }
        }
    } catch (error) {
        console.error('Error updating username:', error);
        toast.error('An error occurred while updating username.');
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonHTML;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Trigger logout redirect only if successful
        if (success) {
             setTimeout(() => {
                 window.location.href = 'logout.php';
             }, 3000);
        }
    }
});

// Initialize Lucide icons on load - important after potentially adding new icons dynamically
document.addEventListener('DOMContentLoaded', () => {
    // Ensure Alpine is ready before initializing icons if Alpine manipulates the DOM early
    if (typeof Alpine !== 'undefined') {
        Alpine.start(); // Ensure Alpine is initialized
    }
     if (typeof lucide !== 'undefined') {
         lucide.createIcons();
    }
    // Re-check for specific dynamic elements if necessary
});

// Also call createIcons in Alpine init if dynamically adding icons within Alpine components
document.addEventListener('alpine:initialized', () => {
     if (typeof lucide !== 'undefined') {
         lucide.createIcons();
    }
}); 