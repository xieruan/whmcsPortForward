<?php

use PortForward\Base;

class PortForward_gost
{
    public $rules;
    public $b;
    public function __construct()
    {
        $this->b = new Base();
    }

    public function PortForward_gost_create($data)
    {        
        $this->b->run('nohup gost -L=:'.$data['nodeport'].'/'.$data['forwardip'].':'.$data['forwardport'].' > /dev/null 2>&1&');
        return true;
    }

    public function PortForward_gost_delete($data)
    {
        $this->b->run("kill $(ps aux |grep gost\ -L=\:".$data['nodeport']." | sed '/grep/d' | awk '{print $2}')");
        return true;
    }

    public function PortForward_gost_olddelete($data)
    {
        $this->b->run("kill $(ps aux |grep gost\ -L=\:".$data['nodeport']." | sed '/grep/d' | awk '{print $2}')");
        return true;
    }

    public function PortForward_gost_checkRepeat($data)
    {
        $res = $this->b->run("ps aux |grep gost\ -L=\:".$data['nodeport']."| sed '/grep/d' | awk '{print $2}'");
        if (!empty(trim($res," "))) {
            return true;
        }
        return false;
    }
}