// Copy-to-Clipboard JavaScript for share link
// Section: Add at the very bottom

function copyTrackerLink() {
    const linkElement = document.querySelector('.share-tracker-box div[style*="font-family:monospace"]');
    if (!linkElement) return;

    const url = linkElement.textContent.trim();
    
    navigator.clipboard.writeText(url).then(() => {
        const originalText = linkElement.textContent;
        linkElement.style.background = '#c8e6c9';
        linkElement.textContent = '? Copied!';
        
        setTimeout(() => {
            linkElement.style.background = '#fff';
            linkElement.textContent = originalText;
        }, 2000);
    }).catch(err => {
        alert('Failed to copy: ' + err);
    });
}
// for sending test pet emails
function sendTestPetAlert() {
    if (!confirm('Send test alert to your neighbor list now?')) return;
    
    jQuery.post(ajaxurl, {
        action: 'iv_send_test_pet_alert',
        nonce: ivSettings.nonce || ''  // We'll add nonce support if needed
    }, function(response) {
        alert(response.data ? response.data : 'Test alert sent successfully!');
    }).fail(function() {
        alert('Error sending test. Check console.');
    });
}

//for sending real message
function sendRealPetAlert() {
    const customMsg = document.getElementById('alert_custom_message').value.trim();
    if (!confirm('🚨 CONFIRM: Send REAL lost pet alert to ALL neighbors in your list? This cannot be undone.')) {
        return;
    }
    
    jQuery.post(ajaxurl, {
        action: 'iv_send_real_pet_alert',
        custom_message: customMsg,
        nonce: ivSettings.nonce || ''
    }, function(response) {
        if (response.success) {
            alert('✅ Real alert sent to ' + response.data.sent + ' neighbors!');
        } else {
            alert('Error: ' + (response.data || 'Unknown error'));
        }
    }).fail(function() {
        alert('Request failed. Check console for details.');
    });
}