<?php
/**
 * @file online/ptz/ptzi.inc.php
 * @brief Base class for camera PTZ interface
 */
namespace AVReg;

class PTZi
{
    public $camurl;
    public function getBounds()
    {
        return array();
    }
    public function getPos()
    {
        return array();
    }
    public function pan($value)
    {
    }
    public function tilt($value)
    {
    }
    public function zoom($value)
    {
    }
    public function focus($value)
    {
    }
    public function home($action)
    {
    }
    public function move($direction, $mode = '')
    {
        if ($direction=="home") {
                return $this->home('go');
        };
        if ($direction=="stop") {
                return $this->stop();
        }
        $pos=$this->getPos();
        if ($direction=="left") {
                $this->pan($pos['pan']-$this->movements[$mode]);
        }
        if ($direction=="right") {
                $this->pan($pos['pan']+$this->movements[$mode]);
        };
        if ($direction=="up") {
                $this->tilt($pos['tilt']+$this->movements[$mode]);
        };
        if ($direction=="down") {
                $this->tilt($pos['tilt']-$this->movements[$mode]);
        };
        if ($direction=="wide") {
                $this->zoom($pos['zoom']-$this->movements[$mode]);
        };
        if ($direction=="tele") {
                $this->zoom($pos['zoom']+$this->movements[$mode]);
        };
        if ($direction=="close") {
                $this->zoom($pos['focus']-$this->movements[$mode]);
        };
        if ($direction=="far") {
                $this->zoom($pos['focus']+$this->movements[$mode]);
        };
    }
    public function stop()
    {
    }
}
