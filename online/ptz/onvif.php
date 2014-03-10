<div class="ptz_area_right">
    <div class="topContainer">
        <div class="ptzPresetsHeader">
            <div class="text">
                <p>ПРЕСЕТЫ</p>
            </div>
            <div class="action">
                <input type="button" class="ptzButton presetAdd" title="Добавить"/>
            </div>
        </div>
        <ul class="ptzPresets">
            <!-- template start -->
            <li class="preset homePreset" title="Home Position">
                <div class="presetName">
                    <p class="name">Home Position</p>
                </div>
                <div class="presetAction">
                    <input type="button" class="presetSetHome ptzButton" title="Установить домашнюю позицию"/>
                </div>
            </li>
            <!-- template end -->
            <!-- template start -->
            <li class="preset normalPreset" data-name="$name" title="$name">
                <div class="presetName">
                    <p class="name">$name</p>
                </div>
                <div class="presetAction">
                    <input type="button" class="presetRemove ptzButton" title="Удалить пресет"/>
                </div>
            </li>
            <!-- template end -->
        </ul>
    </div>
    <div class="bottomContainer">
        <input type="button" class="moveStop ptzButton" value="СТОП"/>
        <br>
        <input type="button" class="settingsShow ptzButton" value="Настройки"/>

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
    <div class="tooSmallIndicator">
        <p>
            увеличьте размер плеера &gt;&gt;
        </p>
    </div>
</div>

<div class="ptz_area_bottom">
    <div class="container">
        <div class="sliders">
            <table class="bottomSliderContainer ptzSliderTilt">
                <tr>
                    <td class="label"><p>TILT</p></td>
                    <td class="button"><input type="button" class="ptzDecreaseMore ptzButton"/></td>
                    <td class="button"><input type="button" class="ptzDecrease ptzButton"/></td>
                    <td class="control"><div id="ptzTiltSlider"></div></td>
                    <td class="button"><input type="button" class="ptzIncrease ptzButton"/></td>
                    <td class="button"><input type="button" class="ptzIncreaseMore ptzButton"/></td>
                </tr>
            </table>
            <table class="bottomSliderContainer ptzSliderZoom">
                <tr>
                    <td class="label"><p>ZOOM</p></td>
                    <td class="button"><input type="button" class="ptzDecreaseMore ptzButton"/></td>
                    <td class="button"><input type="button" class="ptzDecrease ptzButton"/></td>
                    <td class="control"><div id="ptzZoomSlider"></div></td>
                    <td class="button"><input type="button" class="ptzIncrease ptzButton"/></td>
                    <td class="button"><input type="button" class="ptzIncreaseMore ptzButton"/></td>
                </tr>
            </table>
            <table class="bottomSliderContainer ptzSliderPan">
                <tr>
                    <td class="label"><p>PAN</p></td>
                    <td class="button"><input type="button" class="ptzDecreaseMore ptzButton"/></td>
                    <td class="button"><input type="button" class="ptzDecrease ptzButton"/></td>
                    <td class="control"><div id="ptzPanSlider"></div></td>
                    <td class="button"><input type="button" class="ptzIncrease ptzButton"/></td>
                    <td class="button"><input type="button" class="ptzIncreaseMore ptzButton"/></td>
                </tr>
            </table>
        </div>
    </div>
</div>
