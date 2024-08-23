<?php

namespace App\Extensions;

session_start();

class SessionHandler implements \SessionHandlerInterface
{
    public function open($savePath, $sessionName)
    {
    }
    
    public function close()
    {
    }
    
    public function read($sessionId)
    {
        if (isset($_SESSION[$sessionId])) {
            return $_SESSION[$sessionId];
        }
        
        return null;
    }
    
    public function write($sessionId, $data)
    {
        $_SESSION[$sessionId] = $data;
    }
    
    public function destroy($sessionId)
    {
        session_destroy();
    }
    
    public function gc($lifetime)
    {
        session_gc();
    }
}
