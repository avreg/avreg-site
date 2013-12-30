<div id="modal-onvif-profiles" class="jqmWindow">
    <div class="modal-head">
        <span>Подключение медиа профиля</span>
        <a href="#" class="jqmClose">X</a>
    </div>
    <hr>
    <div class="modal-body">
        <ul class="onvif-profile-list">
            <!-- template start -->
            <li>
                <div>
                    <div class="left">
                        <p>Имя профиля: $name</p>

                        <p>Видео кодек: $videoEncoding $videoProfile $videoResolution $frameRateLimit
                            $videoBitRateLimit</p>

                        <p>Аудио кодек: $audio</p>
                    </div>
                    <div class="right"><input type="button" value="Выбрать" data-uri="{$name}"/></div>
                </div>
            </li>
            <!-- template end -->
        </ul>
    </div>
</div>

<div id="modal-profiles-loading" class="jqmWindow">
    <div class="modal-body">
        <p>Загружаем профили..</p>
    </div>
</div>

<script type="text/javascript">
    window.onvifProfiles = new (function ($container) {
        var profilesList = $container.find('.onvif-profile-list'),
            tplProfileEntry = profilesList.html(),
            __onProfileSelect = null,
            $modalLoading = $("#modal-profiles-loading");

        $container.jqm();
        $modalLoading.jqm({
            modal: true
        });

        /**
         * @param {Object} data
         * @param data.InetCam_IP
         * @param data.InetCam_PASSWORD
         * @param data.InetCam_USER
         * @param data.InetCam_http_port
         */
        this.setConnectionInfo = function (data) {
            this.connectionInfo = data || {};
        };

        this.getConnectionInfo = function () {
            return this.connectionInfo;
        }

        /**
         * @param {Boolean} showLoadingState
         * @returns {*}
         */
        this.connect = function (showLoadingState) {
            var self = this,
                connectionInfo = this.getConnectionInfo();

            var request = $.ajax({
                type: "POST",
                url: '/avreg/lib/OnvifClientController.php',
                data: {
                    method: 'getProfiles',
                    data: {
                        host: 'http://' + connectionInfo['InetCam_IP'] + ':' + connectionInfo['InetCam_http_port'],
                        path: '/onvif/device_service',
                        username: connectionInfo['InetCam_USER'],
                        password: connectionInfo['InetCam_PASSWORD']
                    }
                },
                dataType: 'json'
            });

            if (showLoadingState) {
                $modalLoading.jqmShow();
            }

            request
                .done(function (response) {
                    if (window.console) window.console.log(response);
                    self.renderProfiles(response['Profiles']['Profiles']);
                })
                .always(function () {
                    $modalLoading.jqmHide();
                });

            return request;
        }

        this.renderProfiles = function (profiles) {
            profilesList.empty();

            for (var i = 0, I = profiles.length; i < I; i++) {
                var profile = profiles[i],
                    videoProfile;

                if (profile['VideoEncoderConfiguration']['H264']) {
                    videoProfile = profile['VideoEncoderConfiguration']['H264']['H264Profile']
                } else if (profile['VideoEncoderConfiguration']['MPEG4']) {
                    profile['VideoEncoderConfiguration']['MPEG4']['Mpeg4Profile']
                } else {
                    videoProfile = '';
                }

                var $tplEntry = $(
                    tplProfileEntry
                        .replace('$name', profile['Name'])
                        .replace('$videoEncoding', profile['VideoEncoderConfiguration']['Encoding'])
                        .replace('$videoProfile', videoProfile)
                        .replace('$frameRateLimit',
                            profile['VideoEncoderConfiguration']['RateControl']['FrameRateLimit'] + 'fps')
                        .replace('$videoBitRateLimit',
                            profile['VideoEncoderConfiguration']['RateControl']['BitrateLimit'] ?
                                profile['VideoEncoderConfiguration']['RateControl']['BitrateLimit'] + 'kbps'
                                : '')
                        .replace('$videoResolution',
                            profile['VideoEncoderConfiguration']['Resolution']['Width'] + 'x' +
                                profile['VideoEncoderConfiguration']['Resolution']['Height'] + 'px')
                        .replace('$audio', profile['AudioEncoderConfiguration'] ?
                            profile['AudioEncoderConfiguration']['Encoding']
                                + profile['AudioEncoderConfiguration']['Encoding'] + 'kbps'
                            : 'отсутствует')
                );

                $tplEntry.data('profile', profile);

                profilesList.append($tplEntry)
            }
        }

        profilesList.on('click', 'input', function (e) {
            __onProfileSelect($(this).parents('li').data('profile'));
        });

        /**
         * @param {Function} callback
         */
        this.onProfileSelect = function (callback) {
            __onProfileSelect = callback;
        }

        this.showUi = function () {
            $container.jqmShow();
        }
        this.hideUi = function () {
            $container.jqmHide();
        }
    })($('#modal-onvif-profiles'));
</script>
