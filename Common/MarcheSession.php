<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 5/12/17
 * Time: 14:50
 */

namespace AcMarche\Common;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class MarcheSession
{
    protected $session;

    public function __construct()
    {
        $storage = new NativeSessionStorage(array(), new NativeFileSessionHandler());
        $this->session = new Session($storage);

        return $this->session;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }
}
