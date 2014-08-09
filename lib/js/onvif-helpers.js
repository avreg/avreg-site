/* onvif client's helper functions */

/**
 * Make onvif requests to get media URI.
 * @param {Object} _cam_tune_info   camera info: number, host, http_port, login, password
 */
function do_onvif_uri_req(_cam_tune_info) {

    onvifProfiles.setConnectionInfo(_cam_tune_info);
    onvifConnector.setConnectionInfo(_cam_tune_info);

    onvifProfiles.onProfileSelect(function (profile) {
        var camTuneInfo = $.extend({}, _cam_tune_info, onvifConnector.getConnectionInfo());

        var jqxhrGetMediaUri = $.ajax({
            type: "POST",
            url: WwwPrefix + '/lib/OnvifClientController.php',
            data: {
                method: 'getMediaUri',
                data: {
                    origin: 'http://' + camTuneInfo['InetCam_IP'] + ':' + camTuneInfo['InetCam_http_port'],
                    username: camTuneInfo['InetCam_USER'],
                    password: camTuneInfo['InetCam_PASSWD'],
                    ProfileToken: profile['token'],
                    TransportProtocol: 'RTSP',
                    StreamType: 'RTP-Unicast'
                }
            },
            dataType: 'json'
        });

        jqxhrGetMediaUri
            .fail(function(jqXHR, textStatus, errorThrown){
                 alert('GetMediaUri() error: (' + jqXHR.status + ') [' + errorThrown + '] ' + textStatus);
            })
            .done(function(response) {
                var uri = new URI(response['MediaUri']['Uri']);
                var post_data = $.extend({}, camTuneInfo, {
                        InetCam_rtsp_port: uri.port(),
                        rtsp_play: uri.resource(),
                        onvif_profile_token: profile['token']
                    });
                if (typeof profile.VideoEncoderConfiguration !== 'undefined') {
                    $.extend(post_data, {
                        video_src: 'rtsp',
                        geometry: profile.VideoEncoderConfiguration.Resolution.Width + 'x'
                             + profile.VideoEncoderConfiguration.Resolution.Height,
                    });
                }
                if (typeof profile.AudioEncoderConfiguration !== 'undefined') {
                    $.extend(post_data, {
                        audio_src: 'rtsp',
                    });
                }
                var jqxhrSaveParams = $.ajax({
                    type: "POST",
                    url: WwwPrefix + '/admin/cam_params_replace.php',
                    data: post_data,
                    dataType: 'json'
                });

                jqxhrSaveParams
                    .fail(function(jqXHR, textStatus, errorThrown){
                        onvifProfiles.hideUi();
                        var msg;
                        msg = 'SaveParams() error: (' + jqXHR.status + ') [' + errorThrown + '] ' + textStatus;
                        if (jqXHR.readyState == 4 && errorThrown != 'parsererror') {
                           msg += "\n\n";
                           msg += jqXHR.responseText.description;
                        }
                        alert(msg);
                    })
                    .done(function (response) {
                        if (response.status == 'done') {
                            if ($("#iframe-index2").size()) {
                                $("#iframe-index2")[0].contentWindow.location.reload();
                            } else {
                                // if not an iframe
                                if (window.location.href.indexOf('?') > 0) {
                                    window.location.reload(true);
                                } else {
                                    window.location.replace(window.location.href +
                                        '?cam_nr=' + camTuneInfo['cam_nr'] + '&' + 'categories=' + camTuneInfo['categories']);
                                }
                            }
                        } else {
                            onvifProfiles.hideUi();
                            alert('error: ' + response.description);
                        }
                    })
            })
    });

    onvifProfiles.connect(true)
        .done(function () {
            onvifProfiles.showUi();
        })
        .fail(function () {
            // could not get profiles - connection data missing or incorrect
            onvifConnector.showUi(function () {
                // on connection success renew connection data
                onvifProfiles.setConnectionInfo(onvifConnector.getConnectionInfo());
                // hide connection ui
                onvifConnector.hideUi();
                // query profiles from server
                onvifProfiles.connect(true)
                    .done(function () {
                        onvifProfiles.showUi();
                    })
                    .fail(function () {
                        onvifConnector.showUi();
                        onvifConnector.setValidationMessage('Не удалось получить медиа профили с камеры. ' +
                            'Проверьте правильность заполнения формы и повторите запрос.')
                    })
            });
        })
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */

