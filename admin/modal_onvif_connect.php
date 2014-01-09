<div id="modal-onvif-connector" class="jqmWindow">
    <div class="modal-head">
        <span>Подключение к ONVIF-камере</span>
        <a href="#" class="jqmClose">X</a>
    </div>
    <hr>
    <div class="modal-body">
        <div class="validations">

        </div>
        <form id="onvif-connect-info" action="#">
            <div>
                <label>IP адрес:</label><input name="InetCam_IP" type="text"/>
            </div>
            <div>
                <label>Порт:</label><input name="InetCam_http_port" type="text"/>
            </div>
            <div>
                <label>Логин:</label><input name="InetCam_USER" type="text"/>
            </div>
            <div>
                <label>Пароль:</label><input name="InetCam_PASSWD" type="password"/>
            </div>
        </form>
    </div>
    <hr>
    <div class="modal-foot">
        <input type="button" id="onvif-connector-connect" value="Подключиться к камере"/>
    </div>
</div>

<script type="text/javascript">
    window.onvifConnector = new (function ($container) {
        var onvifConnector = this,
            $connectionForm = $container.find('form');

        $container.jqm();

        $connectionForm.on('submit', $.proxy(this.connect, this));
        $container.find('#onvif-connector-connect').on('click', function (e) {
            var $button = $(e.target).prop('disabled', true).val('Подключение...'),
                request = onvifConnector.connect();

            request
                .done(function (response, status, data) {
                    onvifConnector.onConnectionCallback &&
                        onvifConnector.onConnectionCallback(response, onvifConnector.getConnectionInfo());
                }).always(function () {
                    $button.prop('disabled', false).val('Подключиться к камере');
                });
        }.bind(this));


        /**
         * @param {Object} data
         * @param data.InetCam_IP
         * @param data.InetCam_PASSWD
         * @param data.InetCam_USER
         * @param data.InetCam_http_port
         */
        this.setConnectionInfo = function (data) {
            this.connectionInfo = data || {};

            $.each(this.connectionInfo, function (key, value) {
                $connectionForm.find('[name=' + key + ']').val(value);
            });
        };

        this.getConnectionInfo = function () {
            return $connectionForm.serializeObject();
        }

        this.connect = function () {
            var formData = this.getConnectionInfo();
            var request = $.ajax({
                type: "POST",
                url: WwwPrefix + '/lib/OnvifClientController.php',
                data: {
                    method: 'checkConnection',
                    data: {
                        origin: 'http://' + formData['InetCam_IP'] + ':' + formData['InetCam_http_port'],
                        path: '/onvif/device_service',
                        username: formData['InetCam_USER'],
                        password: formData['InetCam_PASSWD']
                    }
                },
                dataType: 'json'
            });

            request
                .done(function(){
                    onvifConnector.setValidationMessage('');
                })
                .fail(function () {
                    if (request.status === 401) {
                        onvifConnector.setValidationMessage(
                            'Пользователь не опознан. Проверьте правильность заполнения формы и повторите запрос.'
                        );
                    } else {
                        onvifConnector.setValidationMessage(
                            'Не удалось подключиться к камере.' +
                            'Проверьте правильность заполнения формы и повторите запрос.'
                        );
                    }
                });

            return request;
        }

        this.setValidationMessage = function (message) {
            $container.find('.validations').text(message);
        }

        this.showUi = function (callback) {
            $container.jqmShow();
            callback && (this.onConnectionCallback = callback);
        }
        this.hideUi = function () {
            $container.jqmHide();
        }

        /**
         * Callback on successful connection.
         * @type {Function|null}
         */
        this.onConnectionCallback = null;

        // initialize with empty data
        this.setConnectionInfo({});
    })($('#modal-onvif-connector'));
</script>
