/* Kaan — reusable live-camera capture for caseta photo inputs.
 * Each `.cam-widget[data-input="<fileInputId>"]` gets a "Tomar foto" button that
 * opens the camera (getUserMedia), captures a frame to a canvas, and sets it as
 * the linked file input's file. Falls back to the file picker where unavailable.
 * Requires a secure context (localhost or HTTPS). */
(function () {
    function initWidget(widget) {
        var fileInput = document.getElementById(widget.dataset.input);
        if (!fileInput) { return; }

        var openBtn = widget.querySelector('.cam-open');
        var video = widget.querySelector('.cam-video');
        var actions = widget.querySelector('.cam-actions');
        var captureBtn = widget.querySelector('.cam-capture');
        var cancelBtn = widget.querySelector('.cam-cancel');
        var canvas = widget.querySelector('.cam-canvas');
        var preview = widget.querySelector('.cam-preview');
        var stream = null;

        function stop() {
            if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
            video.style.display = 'none';
            actions.style.display = 'none';
        }

        openBtn.addEventListener('click', function () {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Tu navegador no permite la cámara aquí; selecciona un archivo.');
                return;
            }
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                .then(function (s) {
                    stream = s; video.srcObject = s; video.play();
                    video.style.display = 'block'; actions.style.display = 'flex';
                })
                .catch(function (e) { alert('No se pudo abrir la cámara: ' + e); });
        });

        cancelBtn.addEventListener('click', stop);

        captureBtn.addEventListener('click', function () {
            canvas.width = video.videoWidth; canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            canvas.toBlob(function (blob) {
                var file = new File([blob], 'captura.jpg', { type: 'image/jpeg' });
                var dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
                preview.src = URL.createObjectURL(blob); preview.style.display = 'block';
                stop();
            }, 'image/jpeg', 0.9);
        });
    }

    document.querySelectorAll('.cam-widget').forEach(initWidget);
})();
