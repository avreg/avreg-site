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
    private $sufix = '';

    public function __construct($server = 'localhost', $port = 11211)
    {
        $this->server = $server;
        $this->port = $port;
        $this->memcache = new \Memcache;
        if ($this->pconnect) {
            $this->memcache->pconnect($this->server, $this->port);
        } else {
            $this->memcache->connect($this->server, $this->port);
        }
    }

    public function __destruct()
    {
        if ($this->pconnect) {
            $this->memcache->close();
        }
    }

    public function setSufix($sufix)
    {
        $this->sufix = $sufix;
    }

    public function get($key)
    {
        $data = $this->memcache->get($this->prefix . $key . $this->sufix);
        return $data;
    }

    public function lock($key, $time = 10)
    {
        $this->memcache->set($this->prefix . $key . $this->sufix . $this->locked, 1, null, $time);
    }

    public function set($key, $value, $compress = false, $time = 0)
    {
        $compress = $compress ? MEMCACHE_COMPRESSED : null;
        $this->memcache->set($this->prefix . $key . $this->sufix, $value, $compress, $time);
        $this->delete($key . $this->sufix . $this->locked);
    }

    public function check($key)
    {
        return true; // temporarily cache disabling FIXME FIXME
        return (bool)$this->memcache->get($this->prefix . $key . $this->sufix . $this->locked);
    }

    public function delete($key, $time = 0)
    {
        $this->memcache->delete($this->prefix . $key . $this->sufix, $time);
    }

    public function flush()
    {
        return $this->memcache->flush();
    }
}
