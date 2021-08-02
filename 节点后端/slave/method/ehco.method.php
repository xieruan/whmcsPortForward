<?php

use PortForward\Base;

class PortForward_ehco
{
    public $rules;
    public $b;
    public function __construct()
    {
        $this->b = new Base();
    }

    public function PortForward_ehco_create($data)
    {        
        $this->b->run('nohup ehco -l 0.0.0.0:'.$data['nodeport'].' -lt ws -r '.$data['forwardip'].':'.$data['forwardport'].' > /dev/null 2>&1&');
        return true;
    }

    public function PortForward_ehco_delete($data)
    {
        $this->b->run("kill $(ps aux |grep ehco\ -l\ 0.0.0.0:".$data['nodeport']." | sed '/grep/d' | awk '{print $2}')");
        return true;
    }

    public function PortForward_ehco_olddelete($data)
    {
        $this->b->run("kill $(ps aux |grep ehco\ -l\ 0.0.0.0:".$data['nodeport']." | sed '/grep/d' | awk '{print $2}')");
        return true;
    }

    public function PortForward_ehco_checkRepeat($data)
    {
        $res = $this->b->run("ps aux |grep ehco\ -l\ 0.0.0.0:".$data['nodeport']."| sed '/grep/d' | awk '{print $2}'");
        if (!empty(trim($res," "))) {
            return true;
        }
        return false;
    }
}
