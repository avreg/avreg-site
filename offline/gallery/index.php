<div id="page">
    <div id="matrix_load" style="display: none;"><img src="gallery/img/loading.gif"></div>

    <div id="sidebar">

        <!-- sidebar inner block -->
        <div class="block">
            <div id="type_event">

                <?php
                if (isset($cookies['type_event'])) {
                    $type = explode(',', trim($cookies['type_event'], ','));
                }
                ?>
                <span class="niceCheck">
                    <input type="checkbox" id="image_type" name="type_event" value="image"
                           checked="<?= (empty($type) || in_array('i', $type)) ? 'checked' : '' ?>"/>
                </span>
                <label for="image_type"><?php print $strimagetype; ?></label><br/>

                <div class="borderBot"></div>
                <span class="niceCheck">
                    <input type="checkbox" id="video_type" name="type_event" value="video"
                           checked="<?= (empty($type) || in_array('v', $type)) ? 'checked' : '' ?>"/>
                </span>
                <label for="video_type"><?php print $strvideotype; ?></label><br/>

                <div class="borderBot"></div>
                <span class="niceCheck">
                    <input type="checkbox" id="audio_type" name="type_event" value="audio"
                           checked="<?= (empty($type) || in_array('a', $type)) ? 'checked' : '' ?>"/>
                </span>
                <label for="audio_type"><?php print $straudiotype; ?></label>
            </div>
            <div id="tree">
                <div id="tree_new">
                </div>
            </div>

            <div id="statistics">
                <span><strong><?php print $strcount_files; ?></strong></span><br/>
                <span><strong><?php print $strsize_files; ?></strong></span><br/>
                <span><strong><?php print $strdate_from; ?></strong></span><br/>
                <span><strong><?php print $strdate_to; ?></strong></span><br/>
            </div>
        </div>
        <!-- end sidebar inner block -->
        <div class="handler" id="handler_vertical" style=""></div>
    </div>
    <div id="content">
        <div class="window">
            <div id="win_top">
                <div class=backToMainDiv align=left>
                    <a class=backToMainDivA href="../index.php">На главную </a>
                </div>
                <div id="select_all_cam">
						<span class="new_Check" style="white-space: nowrap; ">
							<span class="niceCheck">
								<input type="checkbox" id="cam_selector" name="Выбрать/отменить все камеры"
                                       value="select_all">
							</span>
							<label style="float: none !important;" for="cam_selector">
                                <a id="lbl_cam_selector" href="#" class="" title=""> Выбрать/отменить все камеры </a>
                            </label>
						</span>
                </div>
                <div id="cameras_selector" class="field checkboxes">
                    <div class="options">

                        <?php
                        if (isset($cookies['cameras'])) {
                            $cameras = explode(',', trim($cookies['cameras'], ','));
                        }
                        ?>
                        <?php
                        foreach ($GCP_cams_params as $CAM_NR => $PARAM) :?>
                            <span class="new_Check" style="white-space: nowrap; ">
                            <span class="niceCheck"><input type="checkbox" id="camera_<?= $CAM_NR; ?>" name="cameras"
                                value="<?= $CAM_NR; ?>"
                                checked="<?= (empty($cameras) || in_array($CAM_NR, $cameras)) ? 'checked' : '' ?>"/>
                            </span>
                                <?php
                                $name = $name_orig = $PARAM['text_left'];
                                // @codingStandardsIgnoreStart
                                // probably bug in phpcs, as it forcing to use 4 spaces on next block
                                if (mb_strlen($name) > 18) {
                                    $name = mb_substr($name, 0, 15);
                                    $name .= '...';
                                }
                                // @codingStandardsIgnoreEnd
                                $camColor = isset($cookies['camera_' . $CAM_NR . '_color']) && !empty($cookies['camera_'
                                . $CAM_NR . '_color']) ? ' ' . $cookies['camera_' . $CAM_NR . '_color'] . '_font' : '';
                                ?>
                                <label style="float: none !important;" for="camera_<?= $CAM_NR; ?>">
                                    <a href="#<?= $CAM_NR; ?>"
                                       class="set_camera_color<?= $camColor ?>"
                                       title="<?= ($name != $name_orig) ? $name_orig : '' ?>">
                                        <?= $name ?>
                                    </a>
                                </label>
                            </span>
                        <?php
                        endforeach;
                        ?>

                    </div>
                </div>
                <div id="more_cam">...</div>

            </div>
            <div id="win_bot" class="matrix_mode selectBox">
                <div id="list_panel">
                    <div id="scroll_content"></div>
                </div>
                <div id="scroll_v">
                    <div class="scroll_top_v"></div>
                    <div class="scroll_body_v">

                        <div class="scroll_polz_v">
                            <!-- div class="scroll_polz_v_Top" id="scroll_polz_v_Top"></div -->
                            <div class="scroll_polz_v_Middle" id="scroll_polz_v_Middle"></div>
                            <!-- div class="scroll_polz_v_Bottom" id="scroll_polz_v_Bottom"></div -->
                        </div>
                    </div>
                    <div class="scroll_bot_v"></div>
                </div>
            </div>
            <div id="win_bot_detail" class="matrix_mode">
                <a href="#preview">
                    <img id="image_detail" src=""/>
                </a>
            </div>
            <div id="toolbar">
                <div id="toolbar_left">
                    <div class="propotion controls">
                        <span class="niceCheck">
                            <input
                                type="checkbox" id="proportion" name="proportion" value="1"
                                checked="<?= (isset($cookies['proportion']) && $cookies['proportion'] == 'checked') ?
                                    'checked' : '' ?>"
                            />
                        </span>
                        <label for="proportion"><?php print $strproportion; ?></label>
                    </div>
                    <div class="event_info preview controls">
                        <span class="niceCheck">
                            <input
                                type="checkbox" id="info" name="info" value="1"
                                checked="<?= (!isset($cookies['info']) || $cookies['info'] == 'checked') ?
                                    'checked' : '' ?>"
                            />
                        </span>
                        <label for="info"><?php print $strinfo; ?></label>
                    </div>
                </div>
                <div id="toolbar_right">
                    <div id="scale" class="preview controls">
                        <div class="scale_min"></div>
                        <div class="scale_body">
                            <div class="scale_polz"></div>
                        </div>
                        <div class="scale_max"></div>
                    </div>
                    <div id="scale2" class="detail controls">
                        <div class="scale_min"></div>
                        <div class="scale_body">
                            <div class="scale_polz"></div>
                        </div>
                        <div class="scale_max"></div>
                    </div>
                    <div class="controls prevnext">
                        <a class="next" href="#"><img src="gallery/img/arrow_right.png"/></a>
                        <a class="prew" href="#"><img src="gallery/img/arrow_left.png"/></a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div id="overlay"></div>

<div id="cameras_color" class="mod_window">
    <div class="window_title">
        <h2><?php print $strcolorcameras; ?></h2>
    </div>
    <div class="window_body">
        <ul>
            <li class="camera_1_color"></li>
            <li class="camera_2_color"></li>
            <li class="camera_3_color"></li>
            <li class="camera_4_color"></li>
            <li class="camera_5_color"></li>
            <li class="camera_6_color"></li>
            <li class="camera_7_color"></li>
            <li class="camera_8_color"></li>
            <li class="camera_9_color"></li>
            <li class="camera_10_color"></li>
            <li class="camera_11_color"></li>
            <li class="camera_12_color"></li>
            <li class="camera_13_color"></li>
            <li class="camera_14_color"></li>
            <li class="camera_15_color"></li>
            <li class="camera_16_color"></li>
        </ul>
    </div>
    <div class="window_button">
        <button class="close"><?php print $strclose; ?></button>
    </div>
</div>

<div id="nextwindow" class="mod_window next_window">
    <div class="window_title">
        <h2><?php print $strnextwindow; ?></h2>
    </div>
    <div class="window_body">

        <span class="niceCheck"><input type="checkbox" id="checknextwindow" name="checknextwindow" value="1"></span>
        <label for="checknextwindow"><?php print $strchecknextwindow; ?></label>

    </div>
    <div class="window_button">
        <button class="no"><?php print $strno; ?></button>
        <button class="yes"><?php print $stryes; ?></button>
    </div>
</div>

<script type="text/javascript">
    var MediaUrlPref = WwwPrefix + MediaAlias + '\/';

    var ajax_timeout = <?php print isset($conf['ajax_timeout'])? $conf['ajax_timeout']:5 ; ?>;
    var update_tree_timeout = <?php print isset($conf['gallery-update_tree_timeout'])?
    $conf['gallery-update_tree_timeout']: $conf['ajax_timeout']; ?>;

    // формирование глобального объекта перевода
    var lang = {
        all: '<?php print $strall; ?>',
        count_files: '<?php print $strcount_files; ?>',
        size_files: '<?php print $strsize_files; ?>',
        date_from: '<?php print $strdate_from; ?>',
        date_to: '<?php print $strdate_to; ?>',
        camera: '<?php print $strcamera; ?>',
        color_cameras: '<?php print $strcolorcameras; ?>',
        size: '<?php print $strsize; ?>',
        WH: '<?php print $strWH; ?>',
        date: '<?php print $strdate; ?>',
        empty_cameras: '<?php print $strempty_cameras; ?>',
        empty_event: '<?php print $strempty_event; ?>',
        empty_tree: '<?php print $strempty_tree; ?>',
        ajax_timeout: '<?php print $strajax_timeout; ?>'
    };
    // обработка размера файлов
    var units = ['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    function readableFileSize(size) {
        var i = 0;
        while (size >= 1024) {
            size /= 1024;
            ++i;
        }
        return size.toFixed(1) + ' ' + units[i];
    }

    $(function () {

        <?php
        if (isset($conf['aplayerConfig']) &&
            !empty($conf['aplayerConfig']) && is_array($conf['aplayerConfig'])) :?>
            //$.aplayerConfiguration(< ?php print json_encode($conf['aplayerConfig']);?>);
            $.aplayerConfiguration(
            <?php
                $res_conf = aplayer_configurate($conf['aplayerConfig']);
                print json_encode($res_conf);
            ?>);
        <?php
        endif;
        ?>

        // переопределение настроек
        var conf = {
            matrix: {
                limit: <?php print $conf['gallery-limit'];?>,
                event_limit: <?php print isset($conf['gallery-cache_event_limit']) ?
                    $conf['gallery-cache_event_limit'] : 20000;?>,
                min_cell_width: <?php print $conf['gallery-min_cell_width'];?>,
                min_cell_height: <?php print $conf['gallery-min_cell_height'];?>
            },
            show_timeout: <?php print isset($conf['gallery-show_timeout']) ? $conf['gallery-show_timeout'] : 1 ;?>
        };

        $('body').css('overflow', 'hidden');

        // инициализация галереи
        gallery.init(conf);
        document.ready = function (e) {
        }
    });
</script>

