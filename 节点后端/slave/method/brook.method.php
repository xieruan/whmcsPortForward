<?php

use PortForward\Base;

class PortForward_brook
{
    public $rules;
    public $b;
    public function __construct()
    {
        $this->b = new Base();
    }

    public function PortForward_brook_create($data)
    {        
        $this->b->run('nohup brook relay -f :'.$data['nodeport'].' -t '.$data['forwardip'].':'.$data['forwardport'].' > /dev/null 2>&1&');
        return true;
    }

    public function PortForward_brook_delete($data)
    {
        $this->b->run("kill $(ps aux |grep brook\ relay\ -f\ :".$data['nodeport']." | sed '/grep/d' | awk '{print $2}')");
        return true;
    }

    public function PortForward_brook_olddelete($data)
    {
        $this->b->run("kill $(ps aux |grep brook\ relay\ -f\ :".$data['nodeport']." | sed '/grep/d' | awk '{print $2}')");
        return true;
    }

    public function PortForward_brook_checkRepeat($data)
    {
        $res = $this->b->run("ps aux |grep brook\ relay\ -f\ :".$data['nodeport']."\ -t | sed '/grep/d' | awk '{print $2}'");
        if (!empty(trim($res," "))) {
            return true;
        }
        return false;
    }
}