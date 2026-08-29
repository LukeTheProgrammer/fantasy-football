/**
 * Carry frames from the page's world to the service worker.
 *
 * tap.js has to run in the page's world to see ESPN's socket, and that world
 * has no access to chrome.runtime; this one has the reverse problem. The two
 * halves meet at window.postMessage.
 */
window.addEventListener('message', (event) => {
    if (event.source !== window || event.data?.source !== 'espn-draft-tap') {
        return;
    }

    const { direction, url, at, encoding, frame } = event.data;

    // The worker may be asleep or reloading; a dropped frame is not worth
    // breaking the draft room over.
    try {
        chrome.runtime.sendMessage({ direction, url, at, encoding, frame }).catch(() => {});
    } catch (e) {
        // Extension context invalidated — the page outlived a reload.
    }
});
