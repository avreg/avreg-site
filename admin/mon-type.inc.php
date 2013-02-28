<?php
/**
 * 
 * @file admin/mon-type.inc.php
 * @brief Параметры раскладок
 * 
 * Содержит базовые вариатны раскладок камер и функцию для построения раскладки
 * 
 */
/*
 * array(0,1,1,1) - описывает ячейку
 * [0] - номер строки
 * [1] - номер колонки
 * [2] - объединяет ячеек по вертикали
 * [3] - объединяет ячеек по горизонтали
 */
const MAX_CAMS_INTO_LAYOUT = 40;
$onecam_wins = array( array(0,0,1,1) );
$quad_4_4_wins = array(
   array(0,0,1,1), array(0,1,1,1),
   array(1,0,1,1), array(1,1,1,1),
);
$quad_9_9_wins = array(
   array(0,0,1,1), array(0,1,1,1), array(0,2,1,1),
   array(1,0,1,1), array(1,1,1,1), array(1,2,1,1),
   array(2,0,1,1), array(2,1,1,1), array(2,2,1,1),
);
$quad_16_16_wins = array( 
   array(0,0,1,1), array(0,1,1,1), array(0,2,1,1), array(0,3,1,1),
   array(1,0,1,1), array(1,1,1,1), array(1,2,1,1), array(1,3,1,1),
   array(2,0,1,1), array(2,1,1,1), array(2,2,1,1), array(2,3,1,1),
   array(3,0,1,1), array(3,1,1,1), array(3,2,1,1), array(3,3,1,1),
);

$quad_25_25_wins = array( 
   array(0,0,1,1), array(0,1,1,1), array(0,2,1,1), array(0,3,1,1), array(0,4,1,1),
   array(1,0,1,1), array(1,1,1,1), array(1,2,1,1), array(1,3,1,1), array(1,4,1,1),
   array(2,0,1,1), array(2,1,1,1), array(2,2,1,1), array(2,3,1,1), array(2,4,1,1),
   array(3,0,1,1), array(3,1,1,1), array(3,2,1,1), array(3,3,1,1), array(3,4,1,1),
   array(4,0,1,1), array(4,1,1,1), array(4,2,1,1), array(4,3,1,1), array(4,4,1,1),
);

$multi_6_9_wins = array( 
   array(0,0,2,2),                 array(0,2,1,1),
                                   array(1,2,1,1),
   array(2,0,1,1), array(2,1,1,1), array(2,2,1,1),
);
$multi_7_16_wins = array(
   array(0,0,2,2),            array(0,2,2,2),

   array(2,0,2,2),            array(2,2,1,1), array(2,3,1,1),
                              array(3,2,1,1), array(3,3,1,1),
);

$multi_8_16_wins = array( 
   array(0,0,3,3),                                 array(0,3,1,1),
                                                   array(1,3,1,1),
                                                   array(2,3,1,1),
   array(3,0,1,1), array(3,1,1,1), array(3,2,1,1), array(3,3,1,1),
);
$multi_10_16_wins = array(
   array(0,0,2,2),                 array(0,2,2,2),

   array(2,0,1,1), array(2,1,1,1), array(2,2,1,1), array(2,3,1,1),
   array(3,0,1,1), array(3,1,1,1), array(3,2,1,1), array(3,3,1,1),
);
$multi_13_16_wins = array(
   array(0,0,1,1), array(0,1,1,1), array(0,2,1,1), array(0,3,1,1),
   array(1,0,1,1), array(1,1,2,2),                 array(1,3,1,1),
   array(2,0,1,1),                                 array(2,3,1,1),
   array(3,0,1,1), array(3,1,1,1), array(3,2,1,1), array(3,3,1,1),
);
$multi_13_25_wins = array( 
   array(0,0,2,2),                 array(0,2,2,2),                 array(0,4,1,1),
                                                                   array(1,4,1,1),
   array(2,0,2,2),                 array(2,2,2,2),                 array(2,4,1,1),
                                                                   array(3,4,1,1),
   array(4,0,1,1), array(4,1,1,1), array(4,2,1,1), array(4,3,1,1), array(4,4,1,1),
);
$multi_16_25_wins = array( 
	array(0,0,1,1), array(0,1,1,1), array(0,2,2,2),                 array(0,4,1,1),
   array(1,0,1,1), array(1,1,1,1),                                 array(1,4,1,1),
   array(2,0,2,2),                 array(2,2,2,2),                 array(2,4,1,1),
                                                                   array(3,4,1,1),
   array(4,0,1,1), array(4,1,1,1), array(4,2,1,1), array(4,3,1,1), array(4,4,1,1),
);
$multi_17_25_wins = array(
   array(0,0,1,1), array(0,1,1,1), array(0,2,1,1), array(0,3,1,1), array(0,4,1,1),
   array(1,0,1,1), array(1,1,3,3),                                 array(1,4,1,1),
   array(2,0,1,1),                                                 array(2,4,1,1),
   array(3,0,1,1),                                                 array(3,4,1,1),
   array(4,0,1,1), array(4,1,1,1), array(4,2,1,1), array(4,3,1,1), array(4,4,1,1),
);
$multi_19_25_wins = array(
   array(0,0,1,1), array(0,1,1,1), array(0,2,1,1), array(0,3,1,1), array(0,4,1,1),
   array(1,0,2,2),                 array(1,2,2,2),                 array(1,4,1,1),
                                                                   array(2,4,1,1),
   array(3,0,1,1), array(3,1,1,1), array(3,2,1,1), array(3,3,1,1), array(3,4,1,1),
   array(4,0,1,1), array(4,1,1,1), array(4,2,1,1), array(4,3,1,1), array(4,4,1,1),
);
$multi_22_25_wins = array(
   array(0,0,1,1), array(0,1,1,1), array(0,2,1,1), array(0,3,1,1), array(0,4,1,1),
   array(1,0,1,1), array(1,1,2,2),                 array(1,3,1,1), array(1,4,1,1),
   array(2,0,1,1),                                 array(2,3,1,1), array(2,4,1,1),
   array(3,0,1,1), array(3,1,1,1), array(3,2,1,1), array(3,3,1,1), array(3,4,1,1),
   array(4,0,1,1), array(4,1,1,1), array(4,2,1,1), array(4,3,1,1), array(4,4,1,1),
);
$poly_2x3_wins = array(
   array(0,0,1,1), array(0,1,1,1), array(0,2,1,1),
   array(1,0,1,1), array(1,1,1,1), array(1,2,1,1),
);
$poly_3x4_wins = array(
   array(0,0,1,1), array(0,1,1,1), array(0,2,1,1),
   array(1,0,1,1), array(1,1,1,1), array(1,2,1,1),
);
$poly_2x4_wins = array( 
   array(0,0,1,1), array(0,1,1,1), array(0,2,1,1), array(0,3,1,1),
   array(1,0,1,1), array(1,1,1,1), array(1,2,1,1), array(1,3,1,1),
);
$poly_3x4_wins = array( 
   array(0,0,1,1), array(0,1,1,1), array(0,2,1,1), array(0,3,1,1),
   array(1,0,1,1), array(1,1,1,1), array(1,2,1,1), array(1,3,1,1),
   array(2,0,1,1), array(2,1,1,1), array(2,2,1,1), array(2,3,1,1),
);

//-->Wide layouts 

$wide_2_2_wins = array(
array(0,0,1,1), 	array(0,1,1,1), 
);

$wide_3_6_wins = array(
array(0,0,2,2), 					array(0,2,1,1),
									array(1,2,1,1),
);

$wide_6_6_wins = array(
array(0,0,1,1), 	array(0,1,1,1), 	array(0,2,1,1),
array(1,0,1,1), 	array(1,1,1,1), 	array(1,2,1,1),
);

$wide_9_15_wins = array(
array(0,0,2,2), 				 		array(0,2,2,2), 						array(0,4,1,1),
																				array(1,4,1,1),
array(2,0,1,1), 	array(2,1,1,1), 	array(2,2,1,1), 	array(2,3,1,1),		array(2,4,1,1),
);

$wide_5_15_wins = array(
array(0,0,3,2), 					 	array(0,2,3,2), 						array(0,4,1,1),
																				array(1,4,1,1),
																				array(2,4,1,1),
);

$wide_15_15_wins = array(
array(0,0,1,1), 	array(0,1,1,1), 	array(0,2,1,1), 	array(0,3,1,1), 	array(0,4,1,1),
array(1,0,1,1), 	array(1,1,1,1), 	array(1,2,1,1), 	array(1,3,1,1), 	array(1,4,1,1),
array(2,0,1,1), 	array(2,1,1,1), 	array(2,2,1,1), 	array(2,3,1,1), 	array(2,4,1,1),
);

$wide_12_24_wins = array(
array(0,0,1,1), 	array(0,1,2,2), 						array(0,3,2,2), 					 	array(0,5,1,1),
array(1,0,1,1), 																					array(1,5,1,1),
array(2,0,1,1), 	array(2,1,2,2), 					 	array(2,3,2,2), 						array(2,5,1,1),
array(3,0,1,1), 																					array(3,5,1,1),
);

$wide_15_24_wins = array(
array(0,0,1,1), 	array(0,1,1,1), 	array(0,2,1,1), 	array(0,3,1,1), 	array(0,4,1,1), 	array(0,5,1,1),
array(1,0,2,2), 					 	array(1,2,2,2), 						array(1,4,2,2),		

array(3,0,1,1), 	array(3,1,1,1), 	array(3,2,1,1), 	array(3,3,1,1), 	array(3,4,1,1),		array(3,5,1,1),
);

$wide_18_24_wins = array(
array(0,0,1,1), 	array(0,1,1,1), 	array(0,2,1,1), 	array(0,3,1,1), 	array(0,4,1,1), 	array(0,5,1,1),
array(1,0,1,1), 	array(1,1,2,2), 					 	array(1,3,2,2), 						array(1,5,1,1),
array(2,0,1,1), 										 											array(2,5,1,1),
array(3,0,1,1), 	array(3,1,1,1), 	array(3,2,1,1), 	array(3,3,1,1), 	array(3,4,1,1),		array(3,5,1,1),
);

$wide_21_24_wins = array(
array(0,0,1,1), 	array(0,1,1,1), 	array(0,2,1,1), 	array(0,3,1,1), 	array(0,4,1,1), 	array(0,5,1,1),
array(1,0,1,1), 	array(1,1,1,1), 	array(1,2,2,2),							array(1,4,1,1),		array(1,5,1,1),
array(2,0,1,1), 	array(2,1,1,1), 										 	array(2,4,1,1),		array(2,5,1,1),
array(3,0,1,1), 	array(3,1,1,1), 	array(3,2,1,1), 	array(3,3,1,1), 	array(3,4,1,1),		array(3,5,1,1),
);

$wide_24_24_wins = array(
array(0,0,1,1), 	array(0,1,1,1), 	array(0,2,1,1), 	array(0,3,1,1), 	array(0,4,1,1), 	array(0,5,1,1),
array(1,0,1,1), 	array(1,1,1,1), 	array(1,2,1,1), 	array(1,3,1,1), 	array(1,4,1,1),		array(1,5,1,1),
array(2,0,1,1), 	array(2,1,1,1), 	array(2,2,1,1), 	array(2,3,1,1), 	array(2,4,1,1),		array(2,5,1,1),
array(3,0,1,1), 	array(3,1,1,1), 	array(3,2,1,1), 	array(3,3,1,1), 	array(3,4,1,1),		array(3,5,1,1),
);

//ultra wide layouts
$wide_18_18_wins = array(
array(0,0,1,1), 	array(0,1,1,1), 	array(0,2,1,1), 	array(0,3,1,1), 	array(0,4,1,1), 	array(0,5,1,1),
array(1,0,1,1), 	array(1,1,1,1), 	array(1,2,1,1), 	array(1,3,1,1), 	array(1,4,1,1),		array(1,5,1,1),
array(2,0,1,1), 	array(2,1,1,1), 	array(2,2,1,1), 	array(2,3,1,1), 	array(2,4,1,1),		array(2,5,1,1),
);

$wide_9_18_wins = array(
array(0,0,2,2), 						array(0,2,2,2), 						array(0,4,2,2), 	

array(2,0,1,1), 	array(2,1,1,1), 	array(2,2,1,1), 	array(2,3,1,1), 	array(2,4,1,1),		array(2,5,1,1),
);

$wide_12_18_wins = array(
array(0,0,1,1), 	array(0,1,2,2), 						array(0,3,2,2), 						array(0,5,1,1),
array(1,0,1,1), 										 											array(1,5,1,1),
array(2,0,1,1), 	array(2,1,1,1), 	array(2,2,1,1), 	array(2,3,1,1), 	array(2,4,1,1),		array(2,5,1,1),
);

$wide_15_18_wins = array(
array(0,0,1,1), 	array(0,1,1,1), 	array(0,2,2,2), 						array(0,4,1,1), 	array(0,5,1,1),
array(1,0,1,1), 	array(1,1,1,1), 										 	array(1,4,1,1),		array(1,5,1,1),
array(2,0,1,1), 	array(2,1,1,1), 	array(2,2,1,1), 	array(2,3,1,1), 	array(2,4,1,1),		array(2,5,1,1),
);

$wide_34_40_wins = array(
		array(0,0,1,1), array(0,1,1,1), array(0,2,1,1), array(0,3,1,1), array(0,4,1,1), array(0,5,1,1), array(0,6,1,1), array(0,7,1,1),
		array(1,0,1,1), array(1,1,1,1), array(1,2,2,2), 				array(1,4,2,2),					array(1,6,1,1), array(1,7,1,1),
		array(2,0,1,1), array(2,1,1,1), 																array(2,6,1,1), array(2,7,1,1),
		array(3,0,1,1), array(3,1,1,1), array(3,2,1,1), array(3,3,1,1), array(3,4,1,1), array(3,5,1,1), array(3,6,1,1), array(3,7,1,1),
		array(4,0,1,1), array(4,1,1,1), array(4,2,1,1), array(4,3,1,1), array(4,4,1,1), array(4,5,1,1), array(4,6,1,1), array(4,7,1,1)
);

$wide_28_28_wins = array(
		array(0,0,1,1), array(0,1,1,1), array(0,2,1,1), array(0,3,1,1), array(0,4,1,1), array(0,5,1,1), array(0,6,1,1),
		array(1,0,1,1), array(1,1,1,1), array(1,2,1,1), array(1,3,1,1),	array(1,4,1,1),	array(1,5,1,1),	array(1,6,1,1),
		array(2,0,1,1), array(2,1,1,1), array(2,2,1,1), array(2,3,1,1), array(1,4,1,1), array(2,5,1,1),	array(2,6,1,1),
		array(3,0,1,1), array(3,1,1,1), array(3,2,1,1), array(3,3,1,1), array(3,4,1,1), array(3,5,1,1), array(3,6,1,1)
);

$wide_40_40_wins = array(
		array(0,0,1,1), array(0,1,1,1), array(0,2,1,1), array(0,3,1,1), array(0,4,1,1), array(0,5,1,1), array(0,6,1,1), array(0,7,1,1),
		array(1,0,1,1), array(1,1,1,1), array(1,2,1,1), array(1,3,1,1),	array(1,4,1,1),	array(1,5,1,1),	array(1,6,1,1), array(1,7,1,1),
		array(2,0,1,1), array(2,1,1,1), array(2,2,1,1), array(2,3,1,1), array(1,4,1,1),	array(1,5,1,1),	array(2,6,1,1), array(2,7,1,1),
		array(3,0,1,1), array(3,1,1,1), array(3,2,1,1), array(3,3,1,1), array(3,4,1,1), array(3,5,1,1), array(3,6,1,1), array(3,7,1,1),
		array(4,0,1,1), array(4,1,1,1), array(4,2,1,1), array(4,3,1,1), array(4,4,1,1), array(4,5,1,1), array(4,6,1,1), array(4,7,1,1)
);


//-->

/// Список допустимы раскладок
/*
 * 'layout's type name' => array()
 * [0] - кол-во действительных ячеек в раскладке 
 * [1] - колво ячеек в столбце матрицы раскладки
 * [2] - колво ячеек в строке матрицы раскладки
 * [3] - описание каждой ячейки в раскладке
 * [4] - главная ячейка
 * [5] - название: "кол-во камер"
 */
$layouts_defs = array(
   	'ONECAM'    	=> array(  1,  	1,	1,  &$onecam_wins,		1,	&$strONECAM		),
	'WIDE_2_2'		=> array( 	2,	1,	2, 	&$wide_2_2_wins, 	1, 	&$strWide_2_2 	),
    'WIDE_3_6'		=> array( 	3,	2,	3, 	&$wide_3_6_wins, 	1, 	&$strWide_3_6 	),
   	'QUAD_4_4'  	=> array(  4,  	2, 	2,  &$quad_4_4_wins,    1, 	&$strQUAD_4_4   ),
	'MULTI_6_9'		=> array(  6,  	3, 	3,  &$multi_6_9_wins,   1, 	&$strMULTI_6_9  ),
	'WIDE_6_6'		=> array( 	6,	2,	3, 	&$wide_6_6_wins, 	2, 	&$strWide_6_6 	),
   	'MULTI_7_16'	=> array(  7,  	4, 	4,  &$multi_7_16_wins,  1, 	&$strMULTI_7_16 ),
   	'MULTI_8_16'   	=> array(  8,  	4, 	4,  &$multi_8_16_wins,  1, 	&$strMULTI_8_16 ),
   	'QUAD_9_9'		=> array(  9,  	3, 	3,  &$quad_9_9_wins,    5, 	&$strQUAD_9_9   ),
	'WIDE_9_15'	  	=> array(  9,	3,	5, 	&$wide_9_15_wins, 	2, 	&$strWide_9_15 	),
	'WIDE_9_18'  	=> array( 	9, 	3,	6, 	&$wide_9_18_wins, 	2, 	&$strWide_9_18 	),
   	'MULTI_10_16'  	=> array( 10,  	4, 	4,  &$multi_10_16_wins, 1, 	&$strMULTI_10_16 ),
	'WIDE_12_24'  	=> array( 12,	4,	6, 	&$wide_12_24_wins, 	2, 	&$strWide_12_24 ),
	'WIDE_12_18' 	=> array( 12, 	3,	6, 	&$wide_12_18_wins, 	2, 	&$strWide_12_18 ),
   	'MULTI_13_16'  	=> array( 13,  	4, 	4,  &$multi_13_16_wins, 6, 	&$strMULTI_13_16),
   	'MULTI_13_25'  	=> array( 13,  	5, 	5,  &$multi_13_25_wins, 6, 	&$strMULTI_13_25),
	'WIDE_15_15'  	=> array( 15,	3,	5, 	&$wide_15_15_wins, 	8, 	&$strWide_15_15 ),
	'WIDE_15_24'  	=> array( 15,	4,	6, 	&$wide_15_24_wins, 	8, 	&$strWide_15_24 ),
	'WIDE_15_18' 	=> array( 15, 	3,	6, 	&$wide_15_18_wins, 	3, 	&$strWide_15_18 ),
   	'QUAD_16_16'   	=> array( 16,  	4, 	4,  &$quad_16_16_wins,  6, 	&$strQUAD_16_16 ),
   	'MULTI_16_25'  	=> array( 16,  	5, 	5,  &$multi_16_25_wins, 9, 	&$strMULTI_16_25),
   	'MULTI_17_25'  	=> array( 17,  	5, 	5,  &$multi_17_25_wins, 7, 	&$strMULTI_17_25),
	'WIDE_18_24'  	=> array( 18,	4,	6, 	&$wide_18_24_wins, 	9, 	&$strWide_18_24 ),
	'WIDE_18_18'  	=> array( 18, 	3,	6, 	&$wide_18_18_wins, 	9, 	&$strWide_18_18 ),
   	'MULTI_19_25'  	=> array( 19,  	5, 	5,  &$multi_19_25_wins, 7, 	&$strMULTI_19_25),
	'WIDE_21_24'  	=> array( 21,	4,	6, 	&$wide_21_24_wins, 	9, 	&$strWide_21_24 ),
   	'MULTI_22_25'  	=> array( 22,  	5, 	5,  &$multi_22_25_wins, 7, 	&$strMULTI_22_25),
	'WIDE_24_24'  	=> array( 24,	4,	6, 	&$wide_24_24_wins, 	9, 	&$strWide_24_24 ),
   	'QUAD_25_25'   	=> array( 25,  	5, 	5,  &$quad_25_25_wins,  13, &$strQUAD_25_25 ),
	'WIDE_34_40'	=> array( 26,	5,	8,	&$wide_34_40_wins,  	11, &$strWide_34_40),
	'WIDE_28_28'	=> array( 27,	4,	7,	&$wide_28_28_wins,	18, 	&$strWide_28_28),
	'WIDE_40_40'	=> array( 28,	5,	8,	&$wide_40_40_wins,	20, 	&$strWide_40_40)
);

/**
 * 
 * Функция строит таблицу определенной раскладки
 * @param string $mon_type тип раскладки
 * @param int $max_width максимальная ширина раскладки
 * @param array $win_text_array 
 * @param string $win_text
 */
function layout2table ( $mon_type, $max_width, $win_text_array = array(), $win_text = '' )
{
//	 print "<pre>".var_dump($win_text_array)."</pre>";

   if ( !array_key_exists($mon_type, $GLOBALS['layouts_defs']) ) {
         print '<p style="color:' . $GLOBALS['error_color'] . ';">Not Defined Monitors Type. Asc to developers.</p>' ."\n";
         return;
   }

   $l_defs = &$GLOBALS['layouts_defs'][$mon_type];
   
   $brdcol = '#99FFFF';
   $bgcol = '#000099';

   if (empty($max_width)) {
    	$cp =  20; $cs = 0;
    	$brd = 2;
    } else {
    	$cp = 2; $cs = 0;
    	$brd = $max_width >> 6;
		$k = (int) floor( $max_width >> 2 );
		$win =  $k << 2; ; $h = $k * 3;
		$w2 = (int) floor ( $win / 2); $h2 = (int) floor ( $h / 2);
		$w3 = (int) floor ( $win / 3); $h3 = (int) floor ( $h / 3);
		$w4 = (int) floor ( $win / 4); $h4 = (int) floor ( $h / 4);
		$w5 = (int) floor ( $win / 5); $h5 = (int) floor ( $h / 5);
    }	
    
    if(preg_match('/WIDE_/', $mon_type)==1){
    	$tbl_start = '<table cellspacing="0" border="0" cellpadding="0" style="height:90px; " >'."\n";
    }else{
    	$tbl_start = '<table cellspacing="0" border="0" cellpadding="0" style="height:120px; " >'."\n";
    }
    
   
   $tbl_end = '</table>'."\n";
   $r_start = '<tr>'."\n";
   $r_end   = '</tr>'."\n";

   if (empty($max_width))
   {
	$t_start_1 = '<td align="center" valign="middle" bgcolor="'.$bgcol.'"><font color="white"><b>&nbsp;';
	$t_start_2 = &$t_start_1;
	$t_start_3 = &$t_start_1;
	$t_start_4 = &$t_start_1;
	$t_start_5 = &$t_start_1;
    } else {
	$t_start_1 = '<td width="'.$win.'" height="'.$h.'" align="center" valign="middle" bgcolor="'.$bgcol.'"><font color="white"><b>&nbsp;';
	$t_start_2 = '<td width="'.$w2.'" height="'.$h2.'" align="center" valign="middle" bgcolor="'.$bgcol.'"><font color="white"><b>&nbsp;';
	$t_start_3 = '<td width="'.$w3.'" height="'.$h3.'" align="center" valign="middle" bgcolor="'.$bgcol.'"><font color="white"><b>&nbsp;';
	$t_start_4 = '<td width="'.$w4.'" height="'.$h4.'" align="center" valign="middle" bgcolor="'.$bgcol.'"><font color="white"><b>&nbsp;';
	$t_start_5 = '<td width="'.$w5.'" height="'.$h5.'" align="center" valign="middle" bgcolor="'.$bgcol.'"><font color="white"><b>&nbsp;';
    }

   $t_end = '&nbsp;</b></font></td>'."\n";
   
   
   
$max_width/$wins_in_layout = $l_defs[0];
   $rows = $l_defs[1];
   $cols = $l_defs[2];
   $wins = &$l_defs[3];
   $major_win = $l_defs[4] - 1;

   $w1 = $max_width/$cols - 4 /* 2xborder */;
   $h1 =  $w1 * 3 / 4;
   $count_camers_in_layout = count($l_defs[3]);
   print $tbl_start;
   for ($win=0; $win<$count_camers_in_layout; $win++ ) {
      $text_in_win = (!isset($win_text) || @empty($win_text))? @$win_text_array[$win] : $win_text;
      if (!isset($win_text) || @empty($text_in_win))
         $text_in_win = '&nbsp;';
      list($row, $col, $rowspan, $colspan) = $wins[$win];
      if ( $col === 0 ) {
         print $r_start;
         $max_rowspan = 1;
         $min_rowspan = 999;
      }
      if ( $rowspan > $max_rowspan )
            $max_rowspan = $rowspan;
      if ( $rowspan < $min_rowspan )
            $min_rowspan = $rowspan;

      $sz = sprintf('width=%d height=%d', $w1*$colspan, $h1*$rowspan);
      $e = ( $win == $major_win )?'th':'td';
      if ($colspan > 1 || $rowspan > 1 ) {
         print "<$e $sz id=\"win_$win\" class=\"layout\" colspan=\"$colspan\" rowspan=\"$rowspan\"   align=\"center\" valign=\"middle\">$text_in_win</$e>\n";
      } else
         print "<$e $sz id=\"win_$win\" class=\"layout\" align=\"center\" valign=\"middle\">$text_in_win</$e>\n";

      if ( ($col + $colspan) >= $cols ) {
         /* закрываем строку */
         print $r_end;
         if ( $max_rowspan > 1 && $max_rowspan == $min_rowspan)
            print $r_start.$r_end;
      }
   }
   print $tbl_end;
}
?>



