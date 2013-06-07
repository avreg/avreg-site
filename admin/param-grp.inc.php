<?php

if (!isset($params_module_name)) {
	$params_module_name = $wwwdir . '/lang/' . strtolower('params-' . $charset . '.inc.php');
	require_once($params_module_name);
}

$cats_level_array = array();
$categoriesSQL = '^[0-9]+$';
$cats_level_cnt = 0;
$cats_level_in_POST = '0';

$cats = array();
$r_cnt = 0;

function push_cat($cat_id)
{
	$tmp_ar = explode('.', $cat_id);
	$tmp_ar_cnt = sizeof($tmp_ar);
	$aaa = '';
	for ($i = 0; $i < $tmp_ar_cnt; $i++) {
		$aaa .= $tmp_ar[$i];
		array_push($GLOBALS['cats_level_array'], $aaa);
		$aaa .= '.';
	}
} // function push_cat ($cat_id)

function get_cats_level($_cat_id)
{
	if (preg_match('/^\d+$/', $_cat_id)) {
		return 0;
	} elseif (preg_match('/^\d+\.\d+$/', $_cat_id)) {
		return 1;
	} elseif (preg_match(
		'/^\d+\.\d+\.\d+$/',
		$_cat_id
	)
	) {
		return 2;
	} elseif (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $_cat_id)) {
		return 3;
	}
	return -1;
}

function get_cat_name($cur_cat)
{
	$res = '';
	$cat_nr = 0;
	while ($cat_nr < $GLOBALS['r_cnt']) {
		if (0 === strcmp($cur_cat, $GLOBALS['cats'][$cat_nr]['id'])) {
			$res = $GLOBALS['cats'][$cat_nr]['name'];
			break;
		}
		$cat_nr++;
	}
	return $res;
}

function open_cat_tree($cur_cat_id, $_level)
{
	$_array1 = explode('.', $cur_cat_id);
	if (isset($GLOBALS['categories'])) {
		$_array2 = explode('.', $GLOBALS['categories']);
	} else {
		$_array2 = array();
	}
	if ((isset($_array1[$_level]) && isset($_array2[$_level])) && ($_array1[$_level] === $_array2[$_level])) {
		return true;
	} else {
		return false;
	}
}

// выясняем выбрана ли категория и какого уровня
if (isset($categories)) {
	if (!preg_match("/^(\d+|\d+\.?)+$/", $categories)) {
		die ('Wrong query. Your are hacker`s?');
	}
	$cats_level_in_POST = get_cats_level($categories);
	if ($cats_level_in_POST === -1) {
		die ('Wrong query. Your are hacker`s?');
	}
	push_cat($categories);
	$cats_level_cnt = sizeof($cats_level_array);
	if ($cats_level_cnt == 0) {
		die ('Wrong query. Your are hacker`s?');
	}
	$i = 0;
	while ($i < $cats_level_cnt) {
		// $categoriesSQL .= ' OR CODE REGEXP \'^'.str_replace('.','\.',$cats_level_array[$i]).'\.[0-9]+$\'';
		$categoriesSQL .= '|^' . str_replace('.', '\.', $cats_level_array[$i]) . '\.[0-9]+$';
		$i++;
	} //while ( $i < $cats_level_cnt )
} // if ( isset($categories) )

/*
print '<p><pre>'."\n";
var_dump($cats_level_array);
var_dump($categoriesSQL);
print_r($PAR_GROUPS);
print_r($user_status);
print '</pre></p>'."\n";
*/

/* сначала формируем массив  категорий параметров */
reset($PAR_GROUPS);
$ccc = count($PAR_GROUPS);
for ($i = 0; $i < $ccc; $i++) {
	if ($user_status > $PAR_GROUPS[$i]['mstatus']) {
		continue;
	}
	if (!preg_match('/' . $categoriesSQL . '/', $PAR_GROUPS[$i]['id'])) {
		continue;
	}
	$cats[$r_cnt]['id'] = $PAR_GROUPS[$i]['id'];
	$cats[$r_cnt]['name'] = $PAR_GROUPS[$i]['name'];
	$cats[$r_cnt]['desc'] = $PAR_GROUPS[$i]['desc'];
	$cats[$r_cnt]['flags'] = $PAR_GROUPS[$i]['flags'];
	$cats[$r_cnt]['mstatus'] = $PAR_GROUPS[$i]['mstatus'];
	$cats[$r_cnt]['help_page'] = $PAR_GROUPS[$i]['help_page'];
	$cats[$r_cnt]['cats_level'] = intval(get_cats_level($PAR_GROUPS[$i]['id']));
	$r_cnt++;
}

/*
print '<p><pre>'."\n";
var_dump($cats);
print '</pre></p>'."\n";
*/

// выводим таблицу категорий
$cat_nr = 0;
$cam_req = '';
if (isset($cam_nr)) {
	$cam_req .= '&#038;cam_nr=' . $cam_nr;
}

if (isset($cam_name) && !empty($cam_name)) {
	$cam_req .= '&#038;cam_name=' . $cam_name;
}

while ($cat_nr < $r_cnt) {
	$_level = $cats[$cat_nr]['cats_level'];

	if ($WE_IN_DEFS && !($cats[$cat_nr]['flags'] & $F_IN_DEF)) {
		$cat_nr++;
		continue;
	}
	if (!$WE_IN_DEFS && !($cats[$cat_nr]['flags'] & $F_IN_CAM)) {
		$cat_nr++;
		continue;
	}
	print '<div style="margin-left: ' . (string)(20 + $_level * 20) . ';">';
	if (isset($categories) && !strcmp($cats[$cat_nr]['id'], $categories)) {
		print '<img src="' . $conf['prefix'] . '/img/folder.open.gif" border="0">&nbsp;';
		print '<span class="HiLite" title="' . $cats[$cat_nr]['desc'] . '">' . $cats[$cat_nr]['name'] . '</span>';
	} else {
		if (!open_cat_tree($cats[$cat_nr]['id'], $_level)) {
			print '<img src="' . $conf['prefix'] . '/img/folder.gif" border="0">&nbsp;';
			print '<a href="' . $_SERVER["PHP_SELF"] . '?categories=' . $cats[$cat_nr]['id'] . $cam_req . '" title="' . $cats[$cat_nr]['desc'] . '">' . $cats[$cat_nr]['name'] . '</a>';
		} else {
			print '<img src="' . $conf['prefix'] . '/img/folder.open.gif" border="0">&nbsp;';
			print '<a href="' . $_SERVER["PHP_SELF"] . '?categories=' . $cats[$cat_nr]['id'] . $cam_req . '" style="color:' . $error_color . ';" title="' . $cats[$cat_nr]['desc'] . '">' . $cats[$cat_nr]['name'] . '</a>';
		}
	}
	if (!empty($cats[$cat_nr]['help_page'])) {
		print '&nbsp;&nbsp;<sup>(&nbsp;<a href="' . $cats[$cat_nr]['help_page'] . '" target="_blank">help</a>&nbsp;)</sup>';
	}
	print '</div>' . "\n";

	$cat_nr++;
}
?>
