<?php

use PortForward\Base;

class PortForward_tinymapper
{
    public $rules;
    public $b;
    public function __construct()
    {
        $this->b = new Base();
    }

    public function PortForward_tinymapper_create($data)
    {        
        //    tinymapper -l0.0.0.0:1234 -r10.222.2.1:443 -t -u
        $this->b->run('nohup tinymapper -l0.0.0.0:'.$data['nodeport'].' -r'.$data['forwardip'].':'.$data['forwardport'].' -t -u > /dev/null 2>&1&');
        return true;
    }

    public function PortForward_tinymapper_delete($data)
    {
        $this->b->run("kill $(ps aux |grep tinymapper\ -l0.0.0.0:".$data['nodeport']." | sed '/grep/d' | awk '{print $2}')");
        return true;
    }

    public function PortForward_tinymapper_olddelete($data)
    {
        $this->b->run("kill $(ps aux |grep tinymapper\ -l0.0.0.0:".$data['nodeport']." | sed '/grep/d' | awk '{print $2}')");
        return true;
    }

    public function PortForward_tinymapper_checkRepeat($data)
    {
        $res = $this->b->run("ps aux |grep tinymapper\ -l0.0.0.0:".$data['nodeport']."\ -r | sed '/grep/d' | awk '{print $2}'");
        if (!empty(trim($res," "))) {
            return true;
        }
        return false;
    }
}