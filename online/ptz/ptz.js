OnvifPTZControls = function ($container, cameraNumber) {
    var defaultSliderOptions = {
        min: -1,
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


    var move = $.debounce(doMove, 300);

    // setup sliders

    var $zoomSlider = $container.find('#ptzZoomSlider').slider(defaultSliderOptions),
        $panSlider = $container.find('#ptzPanSlider').slider(defaultSliderOptions),
        $tiltSlider = $container.find('#ptzTiltSlider').slider(defaultSliderOptions);

    $zoomSlider.on('slidechange', move);
    $panSlider.on('slidechange', move);
    $tiltSlider.on('slidechange', move);

    // set up dec/inc buttons

    var $tiltDec = $container.find('#ptzTiltDecrease').data('component', 'tilt').data('action', 'dec'),
        $tiltInc = $container.find('#ptzTiltIncrease').data('component', 'tilt').data('action', 'inc'),
        $panDec = $container.find('#ptzPanDecrease').data('component', 'pan').data('action', 'dec'),
        $panInc = $container.find('#ptzPanIncrease').data('component', 'pan').data('action', 'inc'),
        $zoomInc = $container.find('#ptzZoomIncrease').data('component', 'zoom').data('action', 'inc'),
        $zoomDec = $container.find('#ptzZoomDecrease').data('component', 'zoom').data('action', 'dec');

    var incDecButtons = [$tiltInc, $tiltDec, $panDec, $panInc, $zoomDec, $zoomInc];

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
        })
    });

    // set up presets

    var $presets = $container.find('.ptzPresets'),
        presetTpl = $presets.html();

    $presets.empty();
    $container.find('.ptz_area_right, .ptz_area_bottom').on('click', function(e) {e.stopPropagation()});

    $presets.on('click', '.presetName', function(e) {
        gotoPreset($(e.currentTarget).parent('.preset').data('token'));
    });

    // initialize ui

    setControlsEnableState(false);

    var jqxhrGetPtzPosition = getStatus();

    jqxhrGetPtzPosition.done(function (response) {
        setSlidersPosition({
            zoom: response['PTZStatus']['Position']['Zoom']['x'],
            pan: response['PTZStatus']['Position']['PanTilt']['x'],
            tilt: response['PTZStatus']['Position']['PanTilt']['y']
        });

        __lastPosition = getSlidersPosition();
    });

    var jqxhrGetPtzPresets = getPresets();

    jqxhrGetPtzPresets.done(function (response) {
        $presets.empty();

        for (var i = 0, I = response['Presets'].length; i < I; i++) {
            var presetData = response['Presets'][i],
                $preset = $(presetTpl
                    .replace(/\$name/g, presetData['Name'])
                );

            $preset
                .data('position', {
                    zoom: presetData['PTZPosition']['Zoom']['x'],
                    pan: presetData['PTZPosition']['PanTilt']['x'],
                    tilt: presetData['PTZPosition']['PanTilt']['y']
                })
                .data('token', presetData['token'])
                .appendTo($presets);
        }
    });

    $.when(jqxhrGetPtzPosition, jqxhrGetPtzPresets).done(function() {
        setControlsEnableState(true);
    });

    // ajax methods

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

    function getPresets() {
        return $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'getPtzPresets',
                data: {
                    cameraNumber: cameraNumber
                }
            },
            dataType: 'json'
        });
    }

    function doMove() {
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

    function gotoPreset(presetToken) {
        if (__moveInProgress) {
            return;
        }

        var jqXhr = $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'gotoPreset',
                data: {
                    cameraNumber: cameraNumber,
                    presetToken: presetToken
                }
            },
            dataType: 'json'
        });

        __moveInProgress = true;
        setControlsEnableState(false);

        jqXhr
            .done(function (response) {
                setSlidersPosition({
                    zoom: response['PTZStatus']['Position']['Zoom']['x'],
                    pan: response['PTZStatus']['Position']['PanTilt']['x'],
                    tilt: response['PTZStatus']['Position']['PanTilt']['y']
                });

                __lastPosition = getSlidersPosition();
            })
            .always(function () {
                setControlsEnableState(true);
                __moveInProgress = false;
            })
    }

    // ui methods

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
        __moveInProgress = true; // hack - prevent extra move ajax operation
        position.zoom && $zoomSlider.slider('option', 'value', position.zoom);
        position.pan && $panSlider.slider('option', 'value', position.pan);
        position.tilt && $tiltSlider.slider('option', 'value', position.tilt);
        setTimeout(function(){__moveInProgress = false;}, 310); // hack
    }

    this.destruct = function () {
        // todo
    }
};
