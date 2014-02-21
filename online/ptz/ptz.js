OnvifPTZControls = function ($container, cameraNumber, cameraData) {
    var self = this;

    // constants
    var incDecStep = 20,
        pollingTimeout = 300,
        moveDebounceTimeout = 300,
        maxConnectionTries = 5,
        lsKeySettings = 'avreg-ptz-settings';

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
        speedSpaces = {
            position: {
                min: 0,
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
            connectionTries: 0,
            enter: function () {
                setControlsEnableState(false);

                $.when(updatePresets(), updatePosition())
                    .done(function () {
                        states.initial.connectionTries = 0;
                        transitionTo(states.polling);
                    })
                    .fail(function() {
                        transitionTo(states.locked);

                        if (states.initial.connectionTries++ < maxConnectionTries) {
                            transitionTo(states.initial);
                        } else {
                            alert(
                                'Не удается подключиться к камере. Проверьте правильность настроек ONVIF: \n'
                                + '- Имя пользователя и пароль \n'
                                + '- Выбранный медиа-профиль \n'
                            )
                        }
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
        //if (window.console) window.console.log(this.state && this.state.name, state.name);
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

    var $tiltDec = $container.find('.ptzSliderTilt .ptzDecrease').data('component', 'tilt').data('action', 'dec'),
        $tiltInc = $container.find('.ptzSliderTilt .ptzIncrease').data('component', 'tilt').data('action', 'inc'),
        $panDec = $container.find('.ptzSliderPan .ptzDecrease').data('component', 'pan').data('action', 'dec'),
        $panInc = $container.find('.ptzSliderPan .ptzIncrease').data('component', 'pan').data('action', 'inc'),
        $zoomInc = $container.find('.ptzSliderZoom .ptzIncrease').data('component', 'zoom').data('action', 'inc'),
        $zoomDec = $container.find('.ptzSliderZoom .ptzDecrease').data('component', 'zoom').data('action', 'dec');

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

    // set up stop button

    var $moveStop = $container.find('.moveStop');

    $moveStop.on('click', function() {
        self.transitionTo(states.action);

        moveStop()
            .always(function() {
                self.transitionTo(states.polling);
            });
    });

    // set up layout and generics

    var doLayout = function () {
        var $ptzBottomContainer = $container.find('.ptz_area_bottom');

        $ptzBottomContainer.css('top', 0);

        if ($ptzBottomContainer.position().top + $ptzBottomContainer.outerHeight() > $container.outerHeight()) {
            $ptzBottomContainer.css('top', $container.outerHeight() - $ptzBottomContainer.position().top - $ptzBottomContainer.outerHeight() + 'px')
            $ptzBottomContainer.css('opacity', 0.9);
        } else {
            $ptzBottomContainer.css('opacity', 1);
        }
    };

    $(window).on('resize geometrychange ', doLayout);
    doLayout();

    $container.find('.ptz_area_right, .ptz_area_bottom').on('click', function (e) {
        // what goes in ptz area, stays in ptz area..
        e.stopPropagation()
    });

    // set up presets

    var $presets = $container.find('.ptzPresets'),
        tplPresetHome = $presets.find('.homePreset')[0].outerHTML,
        tplPresetNormal = $presets.find('.normalPreset')[0].outerHTML;

    $presets.empty();

    $container.find('.ptz_area_right')
        .on('click', '.normalPreset .presetName', function (e) {
            var preset = $(e.currentTarget).parents('.preset');
            gotoPreset(preset.data('token'), preset.data('position'));
        })
        .on('click', '.normalPreset .presetRemove', function (e) {
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
        })
        .on('click', '.presetAdd', function (e) {
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

    $container.find('.ptz_area_right')
        .on('click', '.homePreset .presetSetHome', function (e) {
            if (self.state === states.action) {
                return;
            }

            transitionTo(states.action);

            setHomePreset()
                .done(function () {
                    updatePresets();
                })
                .always(function () {
                    transitionTo(states.polling);
                });
        })
        .on('click', '.homePreset .presetName', function (e) {
            if (self.state === states.action) {
                return;
            }

            transitionTo(states.action);

            gotoHomePreset()
                .always(function () {
                    transitionTo(states.polling);
                });
        });

    // set up settings modal

    var $settingsModal = $container.find('.modal-onvif-ptz-settings').detach().appendTo('body').jqm(),
        $stPanSpeedSlider, $stTiltSpeedSlider, $stZoomSpeedSlider;

    $container.find('.settingsShow').on('click', function() {
        if (!$settingsModal.data('initialized')) {
            // lazy init
            $stPanSpeedSlider = $settingsModal.find('.modal-ptz-speed-pan').slider($.extend({}, defaultSliderOptions, {
                min: speedSpaces.position.min,
                max: speedSpaces.position.max
            }));
            $stTiltSpeedSlider = $settingsModal.find('.modal-ptz-speed-tilt').slider($.extend({}, defaultSliderOptions, {
                min: speedSpaces.position.min,
                max: speedSpaces.position.max
            }));
            $stZoomSpeedSlider = $settingsModal.find('.modal-ptz-speed-zoom').slider($.extend({}, defaultSliderOptions, {
                min: speedSpaces.zoom.min,
                max: speedSpaces.zoom.max
            }));

            $settingsModal.data('initialized', true);
        }

        var settings = getSettings();

        $stPanSpeedSlider.slider('value',
            typeof settings.speedPan !== 'undefined' ? settings.speedPan : speedSpaces.position.max);
        $stTiltSpeedSlider.slider('value',
            typeof settings.speedTilt !== 'undefined' ? settings.speedTilt : speedSpaces.position.max);
        $stZoomSpeedSlider.slider('value',
            typeof settings.speedZoom !== 'undefined' ? settings.speedZoom : speedSpaces.position.max);

        $settingsModal.jqmShow();
    });

    function getSettings() {
        var settings;

        try {
            settings = JSON.parse(localStorage.getItem(lsKeySettings)) || {};
        } catch (e) {
            settings = {};
        }

        return settings[MD5(JSON.stringify(cameraData))] || {};
    }

    $settingsModal.on('click', '.settings-save', function() {
        var settings;

        try {
            settings = JSON.parse(localStorage.getItem(lsKeySettings));
        } catch (e) {
            settings = {};
        }

        if (localStorage) {
            settings[MD5(JSON.stringify(cameraData))] = {
                speedPan: $stPanSpeedSlider.slider('value'),
                speedTilt: $stTiltSpeedSlider.slider('value'),
                speedZoom: $stZoomSpeedSlider.slider('value')
            };

            localStorage.setItem(lsKeySettings, JSON.stringify(settings));
        } else {
            alert('LocalStorage is not supported');
        }

        $settingsModal.jqmHide();
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

            // home preset
            $presets.append($(tplPresetHome));

            // normal presets
            for (var i = 0, I = response['Presets'].length; i < I; i++) {
                var presetData = response['Presets'][i],
                    $preset = $(tplPresetNormal
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

        // enable stop button
        $moveStop.prop('disabled', false);

        var settings = getSettings();

        var jqXhr = $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'moveAbsolute',
                data: $.extend({
                    cameraNumber: cameraNumber,
                    panSpeed: settings['speedPan'],
                    tiltSpeed: settings['speedTilt'],
                    zoomSpeed: settings['speedZoom']
                }, getSlidersPosition())
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

        // enable stop button
        $moveStop.prop('disabled', false);

        var settings = getSettings();

        var jqXhr = $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'gotoPreset',
                data: {
                    cameraNumber: cameraNumber,
                    presetToken: presetToken,
                    panSpeed: settings['speedPan'],
                    tiltSpeed: settings['speedTilt'],
                    zoomSpeed: settings['speedZoom']
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

    function gotoHomePreset() {
        var settings = getSettings();

        return $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'gotoHomePosition',
                data: {
                    cameraNumber: cameraNumber,
                    panSpeed: settings['speedPan'],
                    tiltSpeed: settings['speedTilt'],
                    zoomSpeed: settings['speedZoom']
                }
            },
            dataType: 'json'
        });
    }

    function setHomePreset() {
        return $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'setHomePosition',
                data: {
                    cameraNumber: cameraNumber
                }
            },
            dataType: 'json'
        });
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

    function moveStop() {
        return $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifPtzController.php',
            data: {
                method: 'moveStop',
                data: {
                    cameraNumber: cameraNumber
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

        $container.find('.settingsShow').prop('disabled', !enabled);
        $container.find('.presetAdd').prop('disabled', !enabled);

        $moveStop.prop('disabled', !enabled);

        $presets.find('input[type=button]').each(function(index, input) {
            $(input).prop('disabled', !enabled);
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
        $settingsModal.remove();
        $settingsModal = null;
    }
};
