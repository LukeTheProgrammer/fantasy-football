const input = document.getElementById('endpoint');
const status = document.getElementById('status');

chrome.storage.local.get(['endpoint', 'lastPost', 'lastError']).then((stored) => {
    input.value = stored.endpoint || '';

    status.textContent = stored.lastError
        ? `Last error: ${stored.lastError}`
        : stored.lastPost
          ? `Last delivered ${new Date(stored.lastPost).toLocaleTimeString()}`
          : 'No frames delivered yet.';
});

input.addEventListener('change', () => {
    chrome.storage.local.set({ endpoint: input.value.trim() });

    status.textContent = 'Saved.';
});
