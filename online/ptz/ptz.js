OnvifPTZControls = function ($container, cameraNumber) {
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
            min: 0,
            max: 1
        }
    };

    var defaultSliderOptions = {
        min: -1,
        max: 1,
        step: 0.000001
    };

    // status variables
    var __blockMoveOperation,
        __lastPosition = {};

    // debounced versions of functions
    var move = $.debounce(doMove, 300);

    // setup sliders

    var $zoomSlider = $container.find('#ptzZoomSlider').slider($.extend({}, defaultSliderOptions, {
            min: coordSpaces.zoom.min,
            max: coordSpaces.zoom.max
        })),
        $panSlider = $container.find('#ptzPanSlider').slider($.extend({}, defaultSliderOptions, {
            min: coordSpaces.pan.min,
            max: coordSpaces.pan.max
        })),
        $tiltSlider = $container.find('#ptzTiltSlider').slider($.extend({}, defaultSliderOptions, {
            min: coordSpaces.tilt.min,
            max: coordSpaces.tilt.max
        }));

    $zoomSlider.on('slidechange', function() { !__blockMoveOperation && move(); });
    $panSlider.on('slidechange', function() { !__blockMoveOperation && move(); });
    $tiltSlider.on('slidechange', function() { !__blockMoveOperation && move(); });

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

    // what goes in ptz area, stays in ptz area..
    $container.find('.ptz_area_right, .ptz_area_bottom').on('click', function(e) {e.stopPropagation()});

    // set up presets

    var $presets = $container.find('.ptzPresets'),
        presetTpl = $presets.html();

    $presets.empty();

    $presets.on('click', '.presetName', function(e) {
        var presetToken = $(e.currentTarget).parents('.preset').data('token'),
            presetPosition = $(e.currentTarget).parents('.preset').data('position');

        gotoPreset(presetToken, presetPosition);
    });
    $presets.on('click', '.presetRemove', function (e) {
        var presetToken = $(e.currentTarget).parents('.preset').data('token');

        if (confirm("Действительно удалить пресет?")) {
            setControlsEnableState(false);

            removePreset(presetToken)
                .done(function () {
                    updatePresets();
                })
                .always(function () {
                    setControlsEnableState(true);
                })
        }
    });
    $container.find('.ptz_area_right, .ptz_area_bottom').on('click', '.presetAdd', function (e) {
        var presetName = prompt("Имя нового пресета");

        if (presetName) {
            setControlsEnableState(false);

            createPreset(presetName)
                .done(function(){
                    updatePresets();
                })
                .always(function(){
                    setControlsEnableState(true);
                })
        }
    });

    // initialize ui

    setControlsEnableState(false);

    $.when(updatePresets(), updatePosition()).done(function() {
        setControlsEnableState(true);
    });

    // action methods

    function updatePosition() {
        return getStatus().done(function (response) {
            setSlidersPosition({
                zoom: response['PTZStatus']['Position']['Zoom']['x'],
                pan: response['PTZStatus']['Position']['PanTilt']['x'],
                tilt: response['PTZStatus']['Position']['PanTilt']['y']
            });

            __lastPosition = getSlidersPosition();
        });
    }

    function updatePresets() {
        return getPresets().done(function (response) {
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
    }

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
        if (__blockMoveOperation) {
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

        __blockMoveOperation = true;
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
                __blockMoveOperation = false;
            });
    }

    function gotoPreset(presetToken, presetPosition) {
        if (__blockMoveOperation) {
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

        __blockMoveOperation = true;
        setControlsEnableState(false);

        jqXhr
            .done(function (response) {
                setSlidersPosition(presetPosition);
                __lastPosition = getSlidersPosition();
            })
            .always(function () {
                setControlsEnableState(true);
                __blockMoveOperation = false;
            })
    }

    function createPreset(presetName) {
        return $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'createPreset',
                data: {
                    cameraNumber: cameraNumber,
                    presetName: presetName
                }
            },
            dataType: 'json'
        });
    }

    function removePreset(presetToken) {
        return $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'removePreset',
                data: {
                    cameraNumber: cameraNumber,
                    presetToken: presetToken
                }
            },
            dataType: 'json'
        });
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
        __blockMoveOperation = true; // hack - prevent extra move ajax operation
        position.zoom && $zoomSlider.slider('option', 'value', position.zoom);
        position.pan && $panSlider.slider('option', 'value', position.pan);
        position.tilt && $tiltSlider.slider('option', 'value', position.tilt);
        __blockMoveOperation = false;
    }

    this.destruct = function () {
        // todo
    }
};
