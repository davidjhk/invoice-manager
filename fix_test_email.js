/**
 * Fix for Test Email functionality in company settings
 * This script adds the missing event handler for the "Send Test Email" button
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Test Email functionality
    const sendTestEmailBtn = document.getElementById('send-test-email');
    const testEmailInput = document.getElementById('test-email');
    const testEmailResult = document.getElementById('test-email-result');
    
    if (sendTestEmailBtn && testEmailInput && testEmailResult) {
        sendTestEmailBtn.addEventListener('click', function() {
            const email = testEmailInput.value.trim();
            
            if (!email) {
                alert('Please enter an email address');
                return;
            }
            
            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address');
                return;
            }
            
            // Show loading state
            const originalText = sendTestEmailBtn.innerHTML;
            sendTestEmailBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            sendTestEmailBtn.disabled = true;
            
            // Prepare form data
            const formData = new FormData();
            formData.append('email', email);
            formData.append('_csrf', $('meta[name=csrf-token]').attr('content'));
            
            // Send AJAX request
            fetch('/company/test-email', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Show result
                testEmailResult.style.display = 'block';
                
                if (data.success) {
                    testEmailResult.className = 'alert alert-success';
                    testEmailResult.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + data.message;
                } else {
                    testEmailResult.className = 'alert alert-danger';
                    testEmailResult.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + data.message;
                    if (data.details) {
                        testEmailResult.innerHTML += '<br><small>' + data.details + '</small>';
                    }
                }
                
                // Reset button
                sendTestEmailBtn.innerHTML = originalText;
                sendTestEmailBtn.disabled = false;
            })
            .catch(error => {
                console.error('Test Email Error:', error);
                
                // Show error
                testEmailResult.style.display = 'block';
                testEmailResult.className = 'alert alert-danger';
                testEmailResult.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>Network error occurred';
                
                // Reset button
                sendTestEmailBtn.innerHTML = originalText;
                sendTestEmailBtn.disabled = false;
            });
        });
    }
});
