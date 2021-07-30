<?php

use PortForward\Base;

class PortForward_iptables
{
    public $rules;
    public $b;
    public function __construct()
    {
        $this->b = new Base();
        $this->rules = $this->b->run('iptables-save -t nat | grep \'Port Forward\'');
    }

    public function PortForward_iptables_create($data)
    {
        $this->b->run('iptables -w -t nat -A PREROUTING -p tcp -m tcp --dport '.$data['nodeport'].' -j DNAT --to-destination '.$data['forwardip'].':'.$data['forwardport'].' -m comment --comment \'Port Forward\'');
        $this->b->run('iptables -w -t nat -A PREROUTING -p udp -m udp --dport '.$data['nodeport'].' -j DNAT --to-destination '.$data['forwardip'].':'.$data['forwardport'].' -m comment --comment \'Port Forward\'');
        $this->b->run('iptables -w -t nat -A POSTROUTING -d '.$data['forwardip'].'/32 -p udp -m udp --dport '.$data['forwardport'].' -j SNAT --to-source '.$data['sourceip'].' -m comment --comment \'Port Forward\'');
        $this->b->run('iptables -w -t nat -A POSTROUTING -d '.$data['forwardip'].'/32 -p tcp -m tcp --dport '.$data['forwardport'].' -j SNAT --to-source '.$data['sourceip'].' -m comment --comment \'Port Forward\'');
        return true;
    }

    public function PortForward_iptables_delete($data)
    {
        $this->b->run('iptables -w -t nat -D PREROUTING -p tcp -m tcp --dport '.$data['nodeport'].' -j DNAT --to-destination '.$data['forwardip'].':'.$data['forwardport'].' -m comment --comment \'Port Forward\'');
        $this->b->run('iptables -w -t nat -D PREROUTING -p udp -m udp --dport '.$data['nodeport'].' -j DNAT --to-destination '.$data['forwardip'].':'.$data['forwardport'].' -m comment --comment \'Port Forward\'');
        $this->b->run('iptables -w -t nat -D POSTROUTING -d '.$data['forwardip'].'/32 -p udp -m udp --dport '.$data['forwardport'].' -j SNAT --to-source '.$data['sourceip'].' -m comment --comment \'Port Forward\'');
        $this->b->run('iptables -w -t nat -D POSTROUTING -d '.$data['forwardip'].'/32 -p tcp -m tcp --dport '.$data['forwardport'].' -j SNAT --to-source '.$data['sourceip'].' -m comment --comment \'Port Forward\'');
        return true;
    }

    public function PortForward_iptables_olddelete($data)
    {
        $this->b->run('iptables -w -t nat -D PREROUTING -p tcp -m tcp --dport '.$data['nodeport'].' -j DNAT --to-destination '.$data['oldforwardip'].':'.$data['forwardport'].' -m comment --comment \'Port Forward\'');
        $this->b->run('iptables -w -t nat -D PREROUTING -p udp -m udp --dport '.$data['nodeport'].' -j DNAT --to-destination '.$data['oldforwardip'].':'.$data['forwardport'].' -m comment --comment \'Port Forward\'');
        $this->b->run('iptables -w -t nat -D POSTROUTING -d '.$data['oldforwardip'].'/32 -p udp -m udp --dport '.$data['forwardport'].' -j SNAT --to-source '.$data['sourceip'].' -m comment --comment \'Port Forward\'');
        $this->b->run('iptables -w -t nat -D POSTROUTING -d '.$data['oldforwardip'].'/32 -p tcp -m tcp --dport '.$data['forwardport'].' -j SNAT --to-source '.$data['sourceip'].' -m comment --comment \'Port Forward\'');
        return true;
    }

    public function PortForward_iptables_checkRepeat($data)
    {
        $res = $this->b->run("iptables-save -t nat | grep \"".$data['nodeport']."\" | grep '\-j DNAT'");
        $res = str_replace(PHP_EOL, '', trim($res));
        //	var_dump($res);
        if (!empty($res)) {
            return true;
        }
        return false;
    }
}
