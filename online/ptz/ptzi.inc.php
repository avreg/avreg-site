<?php
/**
 * @file online/ptz/ptzi.inc.php
 * @brief Base class for camera PTZ interface
 */
class PTZi
{
	var $camurl;
	function get_bounds()
	{
		return array();
	}
	function get_pos()
	{
		return array();
	}
	function pan($value)
	{
	}
	function tilt($value)
	{
	}
	function zoom($value)
	{
	}
	function focus($valut)
	{
	}

};

?>
