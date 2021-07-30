<?php
namespace PortForward;

use \PortForward\Base as Base;

class TrafficCounter
{
    public $dir;
    public $b;
    public function __construct()
    {
        $this->dir = __DIR__;
        $this->b = new Base();
    }

    public function create_traffic($data)
    {
        $counter = $this->dir . "/TrafficCounter.sh";
        $this->b->run('bash '.$counter.' enable '.$data['nodeport'].' '.$data['bandwidth'].' '.$data['forwardport'].' '.$data['forwardip'].' '.$data['method'].' '.$data['nic'].' '.$data['nodebandwidth'].' '.$data['burst']);
    }
    public function delete_traffic($data)
    {
        $counter = $this->dir . "/TrafficCounter.sh";
        $this->b->run('bash '.$counter.' disable '.$data['nodeport'].' '.$data['bandwidth'].' '.$data['forwardport'].' '.$data['forwardip'].' '.$data['method']);
    }
    public function delete_oldtraffic($data)
    {
        $counter = $this->dir . "/TrafficCounter.sh";
        $this->b->run('bash '.$counter.' disable '.$data['nodeport'].' '.$data['bandwidth'].' '.$data['forwardport'].' '.$data['oldforwardip'].' '.$data['method']);
    }


    public function cron($url,$key,$magnification,$token)
    {
        $counter = $this->dir . "/TrafficCounter.sh";
        $json_file = $this->dir . "/../data/service.json";
        $data = json_decode(file_get_contents($json_file),true);
        $tmp = [];
        foreach ($data as $serviceid => $service)
        {
            foreach ($service['port'] as $port)
            {
                $res = json_decode($this->b->run('bash '.$counter.' show '.$port),true);
                $total = $res['1'] + $res['2'];
                $total = 2 * ($this->b->bwFormat($total)) * $magnification;
                //$tmp[$serviceid]['port'] = $port;
                if (!isset($tmp[$serviceid]['traffic'])) {
                    $tmp[$serviceid]['traffic'] = 0;
                } 
                $tmp[$serviceid]['traffic'] += $total;    
            }
        }
        $data = [
            'key' => $key,
            'action' => 'uploadbw',
            'data' => $tmp,
            'token' => $token,
            '0ba7yh8J' => 'aloIJ952xJ',
        ];
        return $this->b->Post($url , $data);
    }

}