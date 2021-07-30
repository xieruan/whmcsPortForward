<?php
namespace PortForward;

use \Curl\Curl as Curl;

class Base
{
    public function Post($url,$data)
    {
        $curl = new Curl();
        $curl->setTimeout('10');
        $curl->setConnectTimeout('10');
	
        $curl->post( $url, $data );

        $result = $curl->rawResponse;
        $curl->close();
        return $result;
    }

     public function rand_str($randLength = 20, $addtime = 1, $includenumber = 0)
     {
         if ($includenumber) {
             $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
         } else {
             $chars = 'abcdefghijklmnopqrstuvwxyz';
         }
         $len = strlen($chars);
         $randStr = '';
         for ($i = 0; $i < $randLength; $i++) {
             $randStr .= $chars[mt_rand(0, $len - 1)];
         }
         $tokenvalue = $randStr;
         if ($addtime) {
             $tokenvalue = $randStr . time();
         }
         return $tokenvalue;
     }

    public function run($comm)
    {
        ob_start();
        passthru($comm);
        $_temp = ob_get_contents();
        ob_end_clean();
        return $_temp;
    }

    public function bwFormat( $num )
    {
        // bytes to MB
        $num /= pow(1024, 2);
        return round($num, 2);
    }

    public function bwFormatb( $size ) {
        $bytes  = array( ' KB', ' MB', ' GB', ' TB' );
        $resVal = '';
        foreach ( $bytes as $val ) {
            $resVal = $val;
            if ( $size >= 1024 ) {
                $size = $size / 1024;
            } else {
                break;
            }
        }

        return round( $size, 1 ) . $resVal;
    }


}
