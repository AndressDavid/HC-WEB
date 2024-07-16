function getFoto(tcButtonGuardar, tcPreviewImg, tcInput, tcWindow, tnFotoWidth, tnFotoHeight){
	
	const tieneSoporteUserMedia = () => !!(navigator.getUserMedia || (navigator.mozGetUserMedia || navigator.mediaDevices.getUserMedia) || navigator.webkitGetUserMedia || navigator.msGetUserMedia);
	const _getUserMedia = (...arguments) => (navigator.getUserMedia || (navigator.mozGetUserMedia || navigator.mediaDevices.getUserMedia) || navigator.webkitGetUserMedia || navigator.msGetUserMedia).apply(navigator, arguments);

	const $video = document.querySelector("#video"),
		$canvas = document.querySelector("#canvas"),
		$listaDeDispositivos = document.querySelector("#listaDeDispositivos");

	const limpiarSelect = () => {
		for (let x = $listaDeDispositivos.options.length - 1; x >= 0; x--)
			$listaDeDispositivos.remove(x);
	};
	const obtenerDispositivos = () => navigator
		.mediaDevices
		.enumerateDevices();

	const llenarSelectConDispositivosDisponibles = () => {

		limpiarSelect();
		obtenerDispositivos()
			.then(dispositivos => {
				const dispositivosDeVideo = [];
				dispositivos.forEach(dispositivo => {
					const tipo = dispositivo.kind;
					if (tipo === "videoinput") {
						dispositivosDeVideo.push(dispositivo);
					}
				});

				if (dispositivosDeVideo.length > 0) {
					dispositivosDeVideo.forEach(dispositivo => {
						const option = document.createElement('option');
						option.value = dispositivo.deviceId;
						option.text = dispositivo.label;
						$listaDeDispositivos.appendChild(option);
					});
				}
			});
	}

    if (!tieneSoporteUserMedia()) {
        alert("Lo siento. El navegador no soporta esta característica");
        return;
    }
    let stream;

    obtenerDispositivos()
        .then(dispositivos => {
            const dispositivosDeVideo = [];

            dispositivos.forEach(function(dispositivo) {
                const tipo = dispositivo.kind;
                if (tipo === "videoinput") {
                    dispositivosDeVideo.push(dispositivo);
                }
            });

            if (dispositivosDeVideo.length > 0) {
                mostrarStream(dispositivosDeVideo[0].deviceId);
            }
        });

    const mostrarStream = idDeDispositivo => {
        _getUserMedia({
                video: {
                    deviceId: idDeDispositivo,
                }
            },
            (streamObtenido) => {
                llenarSelectConDispositivosDisponibles();

                $listaDeDispositivos.onchange = () => {
                    if (stream) {
                        stream.getTracks().forEach(function(track) {
                            track.stop();
                        });
                    }
                    mostrarStream($listaDeDispositivos.value);
                }

                stream = streamObtenido;

                $video.srcObject = stream;
                $video.play();

				$(tcButtonGuardar).click(function(e){
                    $video.pause();

                    let contexto = $canvas.getContext("2d");
                    $canvas.width = tnFotoWidth; //$video.videoWidth;
                    $canvas.height = tnFotoHeight;//$video.videoHeight;
					
					
					lnVideoWidth = (($video.videoHeight * ((tnFotoWidth * 100)/tnFotoHeight))/100);
					contexto.drawImage($video, (($video.videoWidth-lnVideoWidth)/2), 0, lnVideoWidth, $video.videoHeight, 0, 0, $canvas.width, $canvas.height);
					
					let lcFotoCanvas = $canvas.toDataURL('image/png');
					$(tcPreviewImg).attr('src',lcFotoCanvas);
					$(tcInput).empty().val(lcFotoCanvas.replace(/^data:image\/(png|jpg);base64,/, ""));
					$(tcWindow).modal('hide');

                    stream.getTracks().forEach(track => track.stop());
                });
            }, (error) => {
                console.log("Permiso denegado o error: ", error);
            });
    }
};