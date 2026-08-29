/**
 * Batch frames and post them to the app.
 *
 * The relay happens here rather than in the page because the draft room is
 * served over HTTPS: a fetch from the page to http://fantasy.local is blocked
 * as mixed content. An extension service worker is not subject to that, given
 * the host is listed in host_permissions.
 */
const DEFAULT_ENDPOINT = 'http://fantasy.local/api/draft-frames';

const FLUSH_MS = 1000;

const MAX_BATCH = 200;

let queue = [];

let flushing = false;

chrome.runtime.onMessage.addListener((message) => {
    queue.push(message);

    if (queue.length >= MAX_BATCH) {
        flush();
    }
});

const endpoint = async () => {
    const stored = await chrome.storage.local.get('endpoint');

    return stored.endpoint || DEFAULT_ENDPOINT;
};

/**
 * A failed post puts the batch back at the front of the queue: the app being
 * down for a moment mid draft should cost latency, not picks.
 */
const flush = async () => {
    if (flushing || queue.length === 0) {
        return;
    }

    flushing = true;

    const batch = queue.splice(0, MAX_BATCH);

    try {
        const response = await fetch(await endpoint(), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ frames: batch }),
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        await chrome.storage.local.set({ lastPost: Date.now(), lastError: null });
    } catch (e) {
        queue = batch.concat(queue);

        await chrome.storage.local.set({ lastError: String(e), lastErrorAt: Date.now() });
    } finally {
        flushing = false;
    }
};

// The interval covers a worker that stays awake; the alarm is what wakes one
// that has been unloaded. The alarm is created inside a try so a missing
// permission cannot take the interval down with it.
setInterval(flush, FLUSH_MS);

try {
    chrome.alarms.create('flush', { periodInMinutes: 1 / 60 });

    chrome.alarms.onAlarm.addListener(flush);
} catch (e) {
    console.warn('espn-draft-tap: alarms unavailable, relying on the interval', e);
}
