<script>
    (function () {
        if (window.InventoryFaceRecognition) {
            return;
        }

        var faceApiScriptUrl = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js';
        var faceApiModelUrl = 'https://justadudewhohacks.github.io/face-api.js/models';
        var faceApiScriptPromise = null;
        var faceApiModelsPromise = null;

        function loadFaceApiScript() {
            if (window.faceapi) {
                return Promise.resolve(window.faceapi);
            }

            if (faceApiScriptPromise) {
                return faceApiScriptPromise;
            }

            faceApiScriptPromise = new Promise(function (resolve, reject) {
                var existingScript = document.querySelector('script[data-face-api-loader="1"]');

                if (existingScript && window.faceapi) {
                    resolve(window.faceapi);
                    return;
                }

                var script = existingScript || document.createElement('script');

                script.async = true;
                script.src = faceApiScriptUrl;
                script.dataset.faceApiLoader = '1';
                script.onload = function () {
                    if (window.faceapi) {
                        resolve(window.faceapi);
                        return;
                    }

                    reject(new Error('Library face-api.js gagal dimuat.'));
                };
                script.onerror = function () {
                    reject(new Error('Library face-api.js gagal dimuat.'));
                };

                if (!existingScript) {
                    document.head.appendChild(script);
                }
            });

            return faceApiScriptPromise;
        }

        async function loadFaceApiModels() {
            await loadFaceApiScript();

            if (!faceApiModelsPromise) {
                faceApiModelsPromise = Promise.all([
                    window.faceapi.nets.tinyFaceDetector.loadFromUri(faceApiModelUrl),
                    window.faceapi.nets.faceLandmark68Net.loadFromUri(faceApiModelUrl),
                    window.faceapi.nets.faceRecognitionNet.loadFromUri(faceApiModelUrl),
                ]);
            }

            return faceApiModelsPromise;
        }

        function clampShift(value) {
            if (!Number.isFinite(value)) {
                return 0;
            }

            return Math.max(-100, Math.min(100, value));
        }

        function getFrameRatio(frameMode) {
            return frameMode === 'wide' ? 4 / 3 : 1;
        }

        function getCaptureDimensions(captureSize, frameMode) {
            var outputWidth = Math.max(1, Number(captureSize) || 512);
            var targetRatio = getFrameRatio(frameMode);

            return {
                width: outputWidth,
                height: Math.max(1, Math.round(outputWidth / targetRatio)),
                ratio: targetRatio,
            };
        }

        function resolveCropRect(sourceWidth, sourceHeight, targetRatio, horizontalShift, verticalShift) {
            var sourceAspect = sourceWidth / sourceHeight;
            var sourceX = 0;
            var sourceY = 0;
            var cropWidth = sourceWidth;
            var cropHeight = sourceHeight;
            var safeHorizontalShift = clampShift(horizontalShift);
            var safeVerticalShift = clampShift(verticalShift);

            if (sourceAspect > targetRatio) {
                cropHeight = sourceHeight;
                cropWidth = Math.round(cropHeight * targetRatio);

                var horizontalSpace = Math.max(0, sourceWidth - cropWidth);
                sourceX = Math.round((horizontalSpace / 2) + ((safeHorizontalShift / 100) * (horizontalSpace / 2)));
            } else if (sourceAspect < targetRatio) {
                cropWidth = sourceWidth;
                cropHeight = Math.round(cropWidth / targetRatio);

                var verticalSpace = Math.max(0, sourceHeight - cropHeight);
                sourceY = Math.round((verticalSpace / 2) + ((safeVerticalShift / 100) * (verticalSpace / 2)));
            }

            sourceX = Math.max(0, Math.min(sourceWidth - cropWidth, sourceX));
            sourceY = Math.max(0, Math.min(sourceHeight - cropHeight, sourceY));

            return {
                sourceX: sourceX,
                sourceY: sourceY,
                cropWidth: cropWidth,
                cropHeight: cropHeight,
            };
        }

        async function captureFaceData(video, canvas, options) {
            var settings = options || {};
            var includeImage = settings.includeImage !== false;

            await loadFaceApiModels();

            if (!video || !canvas || video.videoWidth <= 0 || video.videoHeight <= 0) {
                return { status: 'camera_not_ready' };
            }

            var captureDimensions = getCaptureDimensions(settings.captureSize, settings.frameMode);
            var cropRect = resolveCropRect(
                video.videoWidth,
                video.videoHeight,
                captureDimensions.ratio,
                settings.horizontalShift,
                settings.verticalShift
            );

            canvas.width = captureDimensions.width;
            canvas.height = captureDimensions.height;

            var context = canvas.getContext('2d');
            context.drawImage(
                video,
                cropRect.sourceX,
                cropRect.sourceY,
                cropRect.cropWidth,
                cropRect.cropHeight,
                0,
                0,
                captureDimensions.width,
                captureDimensions.height
            );

            var detections = await window.faceapi
                .detectAllFaces(
                    canvas,
                    new window.faceapi.TinyFaceDetectorOptions({
                        inputSize: Math.max(160, Math.min(416, Number(settings.detectorInputSize) || 416)),
                        scoreThreshold: 0.5,
                    })
                )
                .withFaceLandmarks()
                .withFaceDescriptors();

            if (!detections || detections.length === 0) {
                return { status: 'no_face' };
            }

            if (detections.length > 1) {
                return { status: 'multiple_faces' };
            }

            var descriptor = detections[0].descriptor ? Array.from(detections[0].descriptor) : [];

            if (descriptor.length !== 128) {
                return { status: 'invalid_descriptor' };
            }

            return {
                status: 'ok',
                descriptor: descriptor,
                imageBase64: includeImage ? canvas.toDataURL('image/jpeg', Number(settings.imageQuality) || 0.85) : '',
            };
        }

        window.InventoryFaceRecognition = {
            loadFaceApiModels: loadFaceApiModels,
            captureFaceData: captureFaceData,
            getCaptureDimensions: getCaptureDimensions,
        };
    })();
</script>