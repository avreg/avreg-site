<?php

namespace Avreg;

class Cache
{
    private $memcache;

    private $locked = ':lock';

    private $pconnect = true;
    private $server = 'localhost';
    private $port = 11211;
    private $prefix = 'gallery:';
    private $debug = false;

    public function __construct($debug = false)
    {
        $this->debug  = $debug;
        $this->memcache = new \Memcache;
        if ($this->pconnect) {
            $this->memcache->pconnect($this->server, $this->port);
        } else {
            $this->memcache->connect($this->server, $this->port);
        }
        if ($this->debug) {
            error_log(sprintf('%s(%s)', __METHOD__, (string)$debug));
        }
    }

    public function __destruct()
    {
        if ($this->pconnect) {
            $this->memcache->close();
        }
        if ($this->debug) {
            error_log(sprintf('%s()', __METHOD__));
        }
    }

    private function keyName($key, $suffix = '')
    {
        $_key = $this->prefix . $key;
        if (!empty($suffix)) {
            $_key .= (string)$suffix;
        }
        return $_key;
    }

    public function get($key)
    {
        $_key = $this->keyName($key);
        $data = $this->memcache->get($_key);
        if ($this->debug) {
            error_log(sprintf('%s("%s") -> %s%s', __METHOD__, $_key, gettype($data), @empty($data) ? '(empty)' : ''));
        }
        return $data;
    }

    public function lock($key, $time = 10)
    {
        $_key = $this->keyName($key, $locked);
        $this->memcache->set($_key, 1, null, $time);
    }

    public function set($key, $value, $compress = false, $time = 0)
    {
        $_key = $this->keyName($key);
        $compress = $compress ? MEMCACHE_COMPRESSED : null;
        $ret_set = $this->memcache->set($_key, $value, $compress, $time);
        if ($this->debug) {
            error_log(sprintf('%s("%s, $value, %s, %d") -> %s', __METHOD__, $_key, (string)$compress, $time, $ret_set));
        }
        $_key = $this->keyName($key, $locked);
        $ret_del_lock = $this->delete($_key);
        if ($this->debug) {
            error_log(sprintf('%s() delete("%s") -> %s', __METHOD__, $_key, $ret_del_lock));
        }
        return $ret_set;
    }

    public function check($key)
    {
        $_key = $this->keyName($key, $locked);
        // return true; // temporarily cache disabling FIXME FIXME
        return (bool)$this->memcache->get($_key);
    }

    public function delete($key, $time = 0)
    {
        $_key = $this->keyName($key);
        $ret = $this->memcache->delete($_key, $time);
        if ($this->debug) {
            error_log(sprintf('%s("%s, %d") -> %s', __METHOD__, $_key, $time, $ret));
        }
        return $ret;
    }

    public function flush()
    {
        $ret = $this->memcache->flush();
        if ($this->debug) {
            error_log(sprintf('%s("") -> %s', __METHOD__, $ret));
        }
        return $ret;
    }
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
