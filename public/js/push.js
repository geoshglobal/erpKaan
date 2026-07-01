/* Kaan — Web Push client. Registers the service worker and manages the
 * per-browser subscription. Wire a button with id="push-toggle" and it will
 * reflect/toggle the subscription state. Requires window.KAAN_VAPID +
 * window.KAAN_BASE set by the page. */
(function () {
    var VAPID = window.KAAN_VAPID;
    var BASE = (window.KAAN_BASE || '/').replace(/\/?$/, '/');
    var btn = document.getElementById('push-toggle');
    var status = document.getElementById('push-status');

    function supported() {
        return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
    }

    function setLabel(state) {
        if (status) { status.textContent = state; }
        if (! btn) { return; }
        if (state === 'activadas') { btn.textContent = '🔕 Desactivar notificaciones push'; btn.dataset.on = '1'; }
        else if (state === 'no soportado') { btn.textContent = 'Push no disponible en este navegador'; btn.disabled = true; }
        else { btn.textContent = '🔔 Activar notificaciones push'; btn.dataset.on = ''; }
    }

    function urlB64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var raw = atob(base64);
        var out = new Uint8Array(raw.length);
        for (var i = 0; i < raw.length; i++) { out[i] = raw.charCodeAt(i); }
        return out;
    }

    function post(path, body) {
        return fetch(BASE + path, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body || {})
        });
    }

    var swReg = null;
    function register() {
        return navigator.serviceWorker.register(BASE + 'sw.js').then(function (reg) { swReg = reg; return reg; });
    }

    function refresh() {
        if (! supported() || ! VAPID) { setLabel('no soportado'); return; }
        register().then(function (reg) {
            return reg.pushManager.getSubscription();
        }).then(function (sub) {
            setLabel(sub ? 'activadas' : 'desactivadas');
        }).catch(function () { setLabel('desactivadas'); });
    }

    function enable() {
        if (! supported() || ! VAPID) { alert('Este navegador no soporta notificaciones push.'); return; }
        Notification.requestPermission().then(function (perm) {
            if (perm !== 'granted') { alert('Debes permitir las notificaciones en el navegador.'); return; }
            register().then(function (reg) {
                return reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlB64ToUint8Array(VAPID)
                });
            }).then(function (sub) {
                return post('push/subscribe', sub.toJSON());
            }).then(function () { setLabel('activadas'); })
              .catch(function (e) { alert('No se pudo activar: ' + e); });
        });
    }

    function disable() {
        if (! swReg) { setLabel('desactivadas'); return; }
        swReg.pushManager.getSubscription().then(function (sub) {
            if (! sub) { setLabel('desactivadas'); return; }
            var endpoint = sub.endpoint;
            return sub.unsubscribe().then(function () {
                return post('push/unsubscribe', { endpoint: endpoint });
            });
        }).then(function () { setLabel('desactivadas'); })
          .catch(function () { setLabel('desactivadas'); });
    }

    if (btn) {
        btn.addEventListener('click', function () {
            if (btn.dataset.on) { disable(); } else { enable(); }
        });
    }

    refresh();
})();
