<?php
/* $params строковый массив, список параметров, которые нужно читать из файла */
function confparse($_conf, $section = null, $path = '/etc/avreg/avreg.conf', $params = null)
{
    $confile = fopen($path, 'r');
    if (false === $confile) {
        return false;
    }
    $skip_section = false;
    $linenr = 0;
    $ret_array = array();
    $res = true;

    while (!feof($confile)) {
        $line = trim(fgets($confile, 1024));
        $linenr++;
        if (empty($line)) {
            continue;
        }

        if (preg_match('/^\s*[;#]/', $line)) {
            continue;
        } /* skip comments */

        if (preg_match('/^([^\s=]+)[\s=]*\{$/', $line, $matches)) {
            # begin section
            if (empty($section) || 0 !== strcasecmp($matches[1], $section)) {
                $skip_section = true;
            }
            continue;
        }

        if (preg_match('/.*\}$/', $line)) {
            $skip_section = false;
            continue;
        }

        if ($skip_section) {
            continue;
        }

        if (1 !== preg_match(
            "/^[\s]*([^\s#;=]+)[\s=]+([\"']?)(.*?)(?<!\\\)([\"']?)\s*$/Su",
            $line,
            $matches
        )
        ) {
            $res = false;
            break;
        }

        $start_quote = & $matches[2];
        $end_quote = & $matches[4];
        if ($start_quote !== $end_quote) {
            $res = false;
            break;
        }

        $param = & $matches[1];
        $value = stripslashes($matches[3]);

        if (is_array($params)) {
            if (false === array_search($param, $params)) {
                continue;
            }
        }

        // нашли параметр
        // printf('file %s:%d : %s => %s (%s)<br>', $path, $linenr, $param, $value, gettype(@$_conf[$param]));
        if (0 === strcasecmp($param, 'include')) {
            // вложенный файл
            $res = confparse($_conf, $section, $value);
            if (!$res) {
                echo "ERROR INCLUDE FILE \"$value\" from $path:$linenr\n";
                $res = false;
                break;
            } else {
                $ret_array = array_merge($ret_array, $res);
            }
        } else {
            /* обычное параметр = значение */
            /* проверяем парамет - а мож это массив */
            if (1 === preg_match("/^([^\[]+)\[([\"']?)([^\]]*?)([\"']?)\]$/Su", $param, $match2)) {
                /* наш параметр -- массив */
                $param = $match2[1];
                $key = $match2[3];
                $vt = gettype(@$_conf[$param]);
                if (0 !== strcasecmp($vt, 'array')) {
                    $res = false;
                    break;
                }
                $ret_array[$param][$key] = $value;
            } else {
                /* простое параметр, не массив */
                /* пробуем установить тип значения с учётом дефолтного $conf[param] */
                $vt = gettype(@$_conf[$param]);
                if ($vt !== 'NULL' && !settype($value, $vt)) {
                    $res = false;
                    break;
                }
                $ret_array[$param] = $value;
            }
        }
    } // while eof

    fclose($confile);

    if ($res) {
        return $ret_array;
    } else {
        // invalid pair param = value
        echo("INVALID LINE in file $path:$linenr => [ $line ]\n");
        return false;
    }
} /* confparse() */
