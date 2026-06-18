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