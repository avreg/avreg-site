<?php

if (file_exists('/etc/init/avreg.conf')) {
    $init_type = 'upstart';
} elseif (is_dir('/run/systemd/system')) {
    $init_type = 'systemd';
} else {
    $init_type = 'sysv';
}

/**
 * Строит init-команду для демона
 */
function get_full_cmd($init_type, $_cmd, $_profile)
{
    $upstart_init = ($init_type === 'upstart');
    if (empty($_profile) /* нет профилей, одна копия демона */) {
        if ($upstart_init && $_cmd == 'reload') {
            return $GLOBALS['conf']['daemon'] . '-worker ' . $_cmd;
        } else {
            return $GLOBALS['conf']['daemon'] . ' ' . $_cmd;
        }
    } else {
        if ($upstart_init) {
            return $GLOBALS['conf']['daemon'] . '-worker ' . $_cmd . ' PROFILE=' . $_profile;
        } else {
            return $GLOBALS['conf']['daemon'] . ' ' . $_cmd . ' ' . $_profile;
        }
    }
}

/**
 * Проверяет статус демонов и выводит на страницу.
 */
function print_daemons_status($init_type, $profile = null)
{
    $upstart_init = ($init_type === 'upstart');
    $daemon_states = array();
    // load avail profiles
    if (!empty($profile)) {
        $profiles = array($profile);
    } else {
        $profiles = & $GLOBALS['EXISTS_PROFILES'];
    }

    print '<div class="warn">' . "\n";
    print '<p>' . $GLOBALS['r_conrol_state'] . ":</p>\n";
    foreach ($profiles as $path) {
        $_profile = basename($path);
        $cmd = get_full_cmd($init_type, 'status', $_profile);
        unset($outs);
        exec($GLOBALS['conf']['sudo'] . ' ' . $cmd, $outs, $retval);
        if ($upstart_init) {
            // avreg-worker (cpu1) start/running, process 6208
            // avreg-worker start/running, process 6208
            $running = (count($outs) > 0 && preg_match('@start/running@', $outs[0]));
        } else {
            $running = ($retval === 0) ? true : false;
        }

        $daemon_states[$_profile] = $running;

        if ($running) {
            print '<span class="HiLiteBig">';
            $st = & $GLOBALS['strRunned'];
        } else {
            print '<span class="HiLiteBigErr">';
            $st = & $GLOBALS['strStopped'];
        }
        if (empty($_profile)) {
            $msg = sprintf('%s - %s', $GLOBALS['videoserv'], $st);
        } else {
            $msg = sprintf('%s-%s - %s', $GLOBALS['videoserv'], $_profile, $st);
        }
        print($msg . '</span>' . "\n");
        if (isset($outs) && is_array($outs)) {
            echo '<pre>';
            echo '# ' . $cmd . "\n";
            if ($init_type === 'systemd') {
                echo 'Status: ' . implode(preg_filter('/^\s*Active:\s*(.*)/', '\1', $outs));
            } else {
                foreach ($outs as $line) {
                    echo $line . "\n";
                }
            }
            echo '</pre>' . "\n";
        }
    }
    print '</div><br />' . "\n";
    return $daemon_states;
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
