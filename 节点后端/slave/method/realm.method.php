<?php

use PortForward\Base;

class PortForward_realm
{
    public $rules;
    public $b;
    public function __construct()
    {
        $this->b = new Base();
    }

    public function PortForward_realm_create($data)
    {        
        $this->b->run('nohup realm -l 0.0.0.0:'.$data['nodeport'].' -r '.$data['forwardip'].':'.$data['forwardport'].' > /dev/null 2>&1&');
        return true;
    }

    public function PortForward_realm_delete($data)
    {
        $this->b->run("kill $(ps aux |grep realm\ -l\ 0.0.0.0:".$data['nodeport']." | sed '/grep/d' | awk '{print $2}')");
        return true;
    }

    public function PortForward_realm_olddelete($data)
    {
        $this->b->run("kill $(ps aux |grep realm\ -l\ 0.0.0.0:".$data['nodeport']." | sed '/grep/d' | awk '{print $2}')");
        return true;
    }

    public function PortForward_realm_checkRepeat($data)
    {
        $res = $this->b->run("ps aux |grep realm\ -l\ 0.0.0.0:".$data['nodeport']."| sed '/grep/d' | awk '{print $2}'");
        if (!empty(trim($res," "))) {
            return true;
        }
        return false;
    }
}
