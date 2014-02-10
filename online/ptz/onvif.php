<div class="ptz_area_right">
    <div class="topContainer">
        <ul class="ptzPresets">
            <!-- template start -->
            <li class="preset" data-name="$name" title="$name">
                <div class="presetName">
                    <p class="name">$name</p>
                </div>
                <div class="presetAction">
                    <input type="button" class="presetRemove" value="-" title="Удалить"/>
                </div>
            </li>
            <!-- template end -->
        </ul>
        <div class="presetAdd">
            <p>Добавить</p>
        </div>
    </div>
    <div class="bottomContainer">
        <input type="button" class="settingsShow" value="Настройки"/>

        <!-- modal will be moved to body eleement -->
        <div class="modal-onvif-ptz-settings jqmWindow" style="display: none">
            <div class="modal-head">
                <span>Настройки PTZ</span>
                <a href="#" class="jqmClose">X</a>
            </div>
            <hr>
            <div class="modal-body">
                <div class="modal-ptz-form">
                    <p>Скорость движения</p>
                    <div class="modal-ptz-row">
                        <div class="modal-ptz-left">
                            PAN
                        </div>
                        <div class="modal-ptz-right">
                            <div class="modal-ptz-speed-pan"></div>
                        </div>
                    </div>
                    <div class="modal-ptz-row">
                        <div class="modal-ptz-left">
                            TILT
                        </div>
                        <div class="modal-ptz-right">
                            <div class="modal-ptz-speed-tilt"></div>
                        </div>
                    </div>
                    <div class="modal-ptz-row">
                        <div class="modal-ptz-left">
                            ZOOM
                        </div>
                        <div class="modal-ptz-right">
                            <div class="modal-ptz-speed-zoom"></div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <p>Настройки будут сохранены в вашем браузере.</p>
            <hr>
            <div class="modal-foot">
                <input type="button" class="settings-save" value="Сохранить настройки"/>
            </div>
        </div>
    </div>
</div>

<div class="ptz_area_bottom">
    <div class="container">
        <div class="sliders">
            <table class="bottomSliderContainer">
                <tr>
                    <td class="label"><p>TILT</p></td>
                    <td class="button"><input type="button" id="ptzTiltDecrease" value="<"/></td>
                    <td class="control"><div id="ptzTiltSlider"></div></td>
                    <td class="button"><input type="button" id="ptzTiltIncrease" value=">"/></td>
                </tr>
            </table>
            <table class="bottomSliderContainer">
                <tr>
                    <td class="label"><p>ZOOM</p></td>
                    <td class="button"><input type="button" id="ptzZoomDecrease" value="<"/></td>
                    <td class="control"><div id="ptzZoomSlider"></div></td>
                    <td class="button"><input type="button" id="ptzZoomIncrease" value=">"/></td>
                </tr>
            </table>
            <table class="bottomSliderContainer">
                <tr>
                    <td class="label"><p>PAN</p></td>
                    <td class="button"><input type="button" id="ptzPanDecrease" value="<"/></td>
                    <td class="control"><div id="ptzPanSlider"></div></td>
                    <td class="button"><input type="button" id="ptzPanIncrease" value=">"/></td>
                </tr>
            </table>
        </div>
    </div>
</div>
