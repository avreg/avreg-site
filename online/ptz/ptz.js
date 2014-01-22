OnvifPTZControls = function ($container, cameraNumber) {
    var defaultSliderOptions = {
        min: 0,
        max: 1,
        step: 0.000001
    }

    var __moveInProgress,
        __lastPosition = {};

    var $zoomSlider = $container.find('#ptzZoomSlider').slider(defaultSliderOptions),
        $panSlider = $container.find('#ptzPanSlider').slider(defaultSliderOptions),
        $tiltSlider = $container.find('#ptzTiltSlider').slider($.extend(
            {},
            defaultSliderOptions,
            {
                orientation: "vertical"
            }
        ));

    setSlidersEnableState(false);

    var jqxhrGetPtzStatus = getStatus();

    jqxhrGetPtzStatus.done(function (response) {
        $zoomSlider.slider('option', 'value', response['PTZStatus']['Position']['Zoom']['x']);
        $panSlider.slider('option', 'value', response['PTZStatus']['Position']['PanTilt']['x']);
        $tiltSlider.slider('option', 'value', response['PTZStatus']['Position']['PanTilt']['y']);

        $zoomSlider.on('slidechange', move);
        $panSlider.on('slidechange', move);
        $tiltSlider.on('slidechange', move);

        __lastPosition = getSlidersPosition();
        setSlidersEnableState(true);
    });

    function getStatus() {
        return $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'getPtzStatus',
                data: {
                    cameraNumber: cameraNumber,
                    ProfileToken: 'balanced_jpeg'
                }
            },
            dataType: 'json'
        });
    }

    function move() {
        if (__moveInProgress) {
            return;
        }

        var jqXhr = $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'moveAbsolute',
                data: $.extend({cameraNumber: cameraNumber}, getSlidersPosition())
            },
            dataType: 'json'
        });

        __moveInProgress = true;
        setSlidersEnableState(false);

        jqXhr
            .done(function () {
                __lastPosition = getSlidersPosition();
            })
            .fail(function () {
                setSlidersPosition(__lastPosition);
            })
            .always(function () {
                setSlidersEnableState(true);
                __moveInProgress = false;
            });
    }

    function setSlidersEnableState(enabled) {
        $zoomSlider.slider(!!enabled ? "enable" : "disable");
        $panSlider.slider(!!enabled ? "enable" : "disable");
        $tiltSlider.slider(!!enabled ? "enable" : "disable");
    }

    function getSlidersPosition() {
        return {
            zoom: $zoomSlider.slider('value'),
            pan: $panSlider.slider('value'),
            tilt: $tiltSlider.slider('value')
        };
    }

    function setSlidersPosition(position) {
        $zoomSlider.slider('option', 'value', position.zoom);
        $panSlider.slider('option', 'value', position.pan);
        $tiltSlider.slider('option', 'value', position.tilt);
    }

    this.destruct = function () {
        // todo
    }
};
