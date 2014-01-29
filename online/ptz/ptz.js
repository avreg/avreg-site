OnvifPTZControls = function ($container, cameraNumber) {
    var defaultSliderOptions = {
        min: 0,
        max: 1,
        step: 0.000001
    };

    var incDecStep = 20;

    var coordSpaces = {
        pan: {
            min: -1,
            max: 1
        },
        tilt: {
            min: -1,
            max: 1
        },
        zoom: {
            min: -1,
            max: 1
        }
    }

    var __moveInProgress,
        __lastPosition = {};

    var $zoomSlider = $container.find('#ptzZoomSlider').slider(defaultSliderOptions),
        $panSlider = $container.find('#ptzPanSlider').slider(defaultSliderOptions),
        $tiltSlider = $container.find('#ptzTiltSlider').slider(defaultSliderOptions);

    var $tiltDec = $container.find('#ptzTiltDecrease').data('component', 'tilt').data('action', 'dec'),
        $tiltInc = $container.find('#ptzTiltIncrease').data('component', 'tilt').data('action', 'inc'),
        $panDec = $container.find('#ptzPanDecrease').data('component', 'pan').data('action', 'dec'),
        $panInc = $container.find('#ptzPanIncrease').data('component', 'pan').data('action', 'inc'),
        $zoomInc = $container.find('#ptzZoomIncrease').data('component', 'zoom').data('action', 'inc'),
        $zoomDec = $container.find('#ptzZoomDecrease').data('component', 'zoom').data('action', 'dec');

    var incDecButtons = [$tiltInc, $tiltDec, $panDec, $panInc, $zoomDec, $zoomInc];

    setControlsEnableState(false);

    // set up sliders
    $.each(incDecButtons, function (i, $button) {
        $button.on('click', function (e) {
            var cmp = $button.data('component'),
                mult = $button.data('action') === 'dec' ? -1 : 1,
                $slider = cmp === 'tilt' ? $tiltSlider :
                    cmp === 'pan' ? $panSlider :
                        cmp === 'zoom' ? $zoomSlider : null;

            $slider.slider(
                'value', $slider.slider('value') + mult * (coordSpaces[cmp].max - coordSpaces[cmp].min) / incDecStep
            );

            e.stopPropagation();
        })
    });


    var jqxhrGetPtzStatus = getStatus();

    jqxhrGetPtzStatus.done(function (response) {
        $zoomSlider.slider('option', 'value', response['PTZStatus']['Position']['Zoom']['x']);
        $panSlider.slider('option', 'value', response['PTZStatus']['Position']['PanTilt']['x']);
        $tiltSlider.slider('option', 'value', response['PTZStatus']['Position']['PanTilt']['y']);

        $zoomSlider.on('slidechange', move);
        $panSlider.on('slidechange', move);
        $tiltSlider.on('slidechange', move);

        __lastPosition = getSlidersPosition();
        setControlsEnableState(true);
    });

    function getStatus() {
        return $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'getPtzStatus',
                data: {
                    cameraNumber: cameraNumber
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
        setControlsEnableState(false);

        jqXhr
            .done(function () {
                __lastPosition = getSlidersPosition();
            })
            .fail(function () {
                setSlidersPosition(__lastPosition);
            })
            .always(function () {
                setControlsEnableState(true);
                __moveInProgress = false;
            });
    }

    function setControlsEnableState(enabled) {
        $zoomSlider.slider(!!enabled ? "enable" : "disable");
        $panSlider.slider(!!enabled ? "enable" : "disable");
        $tiltSlider.slider(!!enabled ? "enable" : "disable");

        $.each(incDecButtons, function (i, $button) {
            $button.prop('disabled', !enabled);
        });
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
