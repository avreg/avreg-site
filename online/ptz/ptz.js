OnvifPTZControls = function ($container, cameraNumber) {
    var defaultSliderOptions = {
        min: 0,
        max: 1,
        step: 0.000001
    }

    var $zoomSlider = $container.find('#ptzZoomSlider').slider(defaultSliderOptions),
        $panSlider = $container.find('#ptzPanSlider').slider(defaultSliderOptions),
        $tiltSlider = $container.find('#ptzTiltSlider').slider($.extend(
            {},
            defaultSliderOptions,
            {
                orientation: "vertical"
            }
        ));

    var jqxhrGetPtzStatus = getStatus();

    jqxhrGetPtzStatus.done(function (response) {
        $zoomSlider.slider('option', 'value', response['PTZStatus']['Position']['Zoom']['x']);
        $panSlider.slider('option', 'value', response['PTZStatus']['Position']['PanTilt']['x']);
        $tiltSlider.slider('option', 'value', response['PTZStatus']['Position']['PanTilt']['y']);

        $zoomSlider.on('slidechange', move);
        $panSlider.on('slidechange', move);
        $tiltSlider.on('slidechange', move);
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

    var __moveInProgress,
        __lastPosition = {};

    function move() {
        if (__moveInProgress) {
            return;
        }

        __lastPosition = {
            zoom: $zoomSlider.slider('value'),
            pan: $panSlider.slider('value'),
            tilt: $tiltSlider.slider('value')
        }

        var jqXhr = $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'moveAbsolute',
                data: $.extend({cameraNumber: cameraNumber}, __lastPosition)
            },
            dataType: 'json'
        });

        jqXhr
            .done(function () {

            })
            .fail(function () {

            })
            .always(function () {
                __moveInProgress = false;
            });
    }

    this.destruct = function () {
        // todo
    }
};
