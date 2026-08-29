/**
 * Tee every frame the ESPN draft room's websocket carries.
 *
 * ESPN allows one socket per team: a second connection evicts the browser from
 * the draft room, which rules out connecting from the app directly. So this
 * wraps the room's own socket instead and copies what passes through. It never
 * holds a frame back, rewrites one, or opens a connection of its own — the
 * room behaves exactly as it would without the extension.
 *
 * This runs in the page's own world (content scripts get an isolated one, where
 * patching window.WebSocket would patch a copy ESPN never sees) at
 * document_start, before ESPN's bundle constructs its socket.
 */
(() => {
    const DRAFT_HOST = 'fantasydraft.espn.com';

    const Native = window.WebSocket;

    /**
     * Frames reach the isolated world by postMessage, the only channel the two
     * share; bridge.js listens for this type and forwards it to the worker.
     */
    const emit = (direction, url, data) => {
        window.postMessage(
            { source: 'espn-draft-tap', direction, url, at: Date.now(), ...data },
            window.location.origin
        );
    };

    /**
     * INIT arrives as binary. Base64 keeps it intact — stringifying a payload
     * that is not UTF-8 silently corrupts it before we ever decode it.
     */
    const encode = (payload) => {
        if (typeof payload === 'string') {
            return Promise.resolve({ encoding: 'text', frame: payload });
        }

        const blob = payload instanceof Blob ? payload : new Blob([payload]);

        return blob.arrayBuffer().then((buffer) => {
            let binary = '';

            new Uint8Array(buffer).forEach((byte) => {
                binary += String.fromCharCode(byte);
            });

            return { encoding: 'base64', frame: btoa(binary) };
        });
    };

    const relay = (direction, url, payload) => {
        try {
            encode(payload).then((data) => emit(direction, url, data));
        } catch (e) {
            emit(direction, url, { encoding: 'error', frame: String(e) });
        }
    };

    window.WebSocket = new Proxy(Native, {
        construct(target, args) {
            const socket = new target(...args);
            const url = String(args[0] ?? '');

            if (!url.includes(DRAFT_HOST)) {
                return socket;
            }

            emit('open', url, { encoding: 'text', frame: 'CONNECT' });

            socket.addEventListener('message', (event) => relay('recv', url, event.data));
            socket.addEventListener('close', (event) =>
                emit('close', url, { encoding: 'text', frame: `CLOSE ${event.code} ${event.reason}` })
            );

            const send = socket.send.bind(socket);

            socket.send = (payload) => {
                relay('sent', url, payload);

                return send(payload);
            };

            return socket;
        },
    });
})();
