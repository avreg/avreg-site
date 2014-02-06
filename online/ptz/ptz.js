OnvifPTZControls = function ($container, cameraNumber) {
    var self = this;

    // constants
    var incDecStep = 20,
        pollingTimeout = 150,
        moveDebounceTimeout = 300;

    // defaults
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
        },
        defaultSliderOptions = {
            min: -1,
            max: 1,
            step: 0.000001
        };

    // status variables
    var __lastPosition = {},
        __pollInterval;

    // state machine
    this.state = null;

    var states = {
        /**
         * Initial state
         */
        'initial': {
            name: 'initial',
            enter: function () {
                setControlsEnableState(false);

                $.when(updatePresets(), updatePosition())
                    .done(function () {
                        transitionTo(states.polling);
                    })
                    .fail(function() {
                        transitionTo(states.locked);
                        transitionTo(states.initial);
                    });
            },
            exit: function () {}
        },
        /**
         * Polling for position
         */
        'polling': {
            name: 'polling',
            enter: function () {
                setControlsEnableState(true);

                var __jqXhrPoll;

                var poller = function () {
                    if (self.state === states.polling && (!__jqXhrPoll || __jqXhrPoll.state() !== 'pending')) {
                        __jqXhrPoll = updatePosition();
                    }
                };

                poller();
                __pollInterval = setInterval(poller, pollingTimeout);
            },
            exit: function () {
                clearInterval(__pollInterval);
            }
        },
        /**
         * Waiting for user input
         */
        'input': {
            name: 'input',
            enter: function() {
            },
            exit: function() {
            }
        },
        /**
         * Controls are locked
         */
        'locked': {
            name: 'locked',
            enter: function() {
                setControlsEnableState(false);
            },
            exit: function() {
                setControlsEnableState(true);
            }
        },
        /**
         * Async action in progress
         */
        'action': {
            name: 'action',
            enter: function () {
                setControlsEnableState(false);
                // cache current position to be able to revert position on action fail
                __lastPosition = getSlidersPosition();
            },
            exit: function () {}.bind(this)
        }
    };

    this.transitionTo = function(state) {
        if (state === this.state) {
            return;
        }

        this.state && this.state.exit && this.state.exit();
        this.__oldState = this.state;
        this.state = state;
        this.state.enter();
    };

    var transitionTo = this.transitionTo.bind(this); // alias
    var transitionBack = function(){ this.transitionTo(this.__oldState); }.bind(this); // alias

    // debounced versions of functions

    var move = $.debounce(doMove, moveDebounceTimeout);

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

    var onSliderChange = function (e) {
        var $slider = $(e.currentTarget);

        if (!$slider.slider('option', 'disabled') && self.state !== states.action && self.state !== states.locked) {
            transitionTo(states.input);
            move();
        }
    };
    $zoomSlider.on('slidechange', onSliderChange);
    $panSlider.on('slidechange', onSliderChange);
    $tiltSlider.on('slidechange', onSliderChange);

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

    $container.find('.ptz_area_right, .ptz_area_bottom').on('click', function (e) {
        e.stopPropagation()
    });

    // set up presets

    var $presets = $container.find('.ptzPresets'),
        presetTpl = $presets.html();

    $presets.empty();

    $presets.on('click', '.presetName', function (e) {
        var preset = $(e.currentTarget).parents('.preset');
        gotoPreset(preset.data('token'), preset.data('position'));
    });
    $presets.on('click', '.presetRemove', function (e) {
        var presetToken = $(e.currentTarget).parents('.preset').data('token');

        if (self.state === states.action) {
            return;
        }

        if (confirm("Действительно удалить пресет?")) {
            transitionTo(states.action);

            removePreset(presetToken)
                .done(function () {
                    updatePresets();
                })
                .always(function () {
                    transitionTo(states.polling);
                })
        }
    });
    $container.find('.ptz_area_right').on('click', '.presetAdd', function (e) {
        if (self.state === states.action) {
            return;
        }

        var presetName = prompt("Имя нового пресета");

        if (presetName) {
            transitionTo(states.action);

            createPreset(presetName)
                .done(function () {
                    updatePresets();
                })
                .always(function () {
                    transitionTo(states.polling);
                })
        }
    });

    // initialize ui

    transitionTo(states.initial);

    // action methods

    function updatePosition() {
        return getStatus().done(function (response) {
            if ( self.state === states.input || self.state === states.action) {
                // do nothing if we're in the middle of the actions; need to deal with polling & debounce combination
                return;
            }

            setSlidersPosition({
                zoom: response['PTZStatus']['Position']['Zoom']['x'],
                pan: response['PTZStatus']['Position']['PanTilt']['x'],
                tilt: response['PTZStatus']['Position']['PanTilt']['y']
            });
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
        if (self.state === states.action) {
            return;
        }

        transitionTo(states.action);

        var jqXhr = $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'moveAbsolute',
                data: $.extend({cameraNumber: cameraNumber}, getSlidersPosition())
            },
            dataType: 'json'
        });

        jqXhr
            .fail(function () {
                setSlidersPosition(__lastPosition);
            })
            .always(function () {
                transitionTo(states.polling);
            });
    }

    function gotoPreset(presetToken, presetPosition) {
        if (self.state === states.action) {
            return;
        }

        transitionTo(states.action);

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

        jqXhr
            .done(function (response) {
                // commented out, see issue https://github.com/yojeek/avreg-site/issues/6
                //setSlidersPosition(presetPosition);
            })
            .always(function () {
                transitionTo(states.polling);
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
        setControlsEnableState(false);
        position.zoom && $zoomSlider.slider('option', 'value', position.zoom);
        position.pan && $panSlider.slider('option', 'value', position.pan);
        position.tilt && $tiltSlider.slider('option', 'value', position.tilt);
        setControlsEnableState(true);
    }

    this.destruct = function () {
        this.state.exit();
    }
};
