OnvifPTZControls = function($container, cameraNumber) {
    var defaultSliderOptions = {
        min: 0,
        max: 1,
        step: 0.001
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

    var jqxhrGetPtzStatus = $.ajax({
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

    jqxhrGetPtzStatus.done(function(response) {
        $zoomSlider.slider('option', 'value', response['PTZStatus']['Position']['Zoom']['x']);
        $panSlider.slider('option', 'value', response['PTZStatus']['Position']['PanTilt']['x']);
        $tiltSlider.slider('option', 'value', response['PTZStatus']['Position']['PanTilt']['y']);
    });

    window.$tiltSlider = $tiltSlider;

    this.destruct = function() {
        // todo
    }
};
