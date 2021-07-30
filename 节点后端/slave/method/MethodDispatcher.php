<?php
namespace PortForward;

class MethodDispatcher
{
    public function Dispatch($method, $action, $vars)
    {
        // 定义类名与动作名
        $ClassName = 'PortForward_' . $method;
        $MethodName = 'PortForward_' . $method . '_' . $action;
        if (!class_exists($ClassName)) {
            // 加载 类文件
            if (file_exists(__DIR__ . "/" . $method . ".method.php")) {
                require __DIR__ . "/" . $method . ".method.php";
                // 调用类
                $MethodClass = new $ClassName();
                if (is_callable(array($MethodClass, $MethodName))) {
                    return $MethodClass->$MethodName($vars);
                } else {
                    return json_encode([
                        'status' => 'failed',
                        'message' => 'action not found'
                    ]);
                }
            } else {
                return json_encode([
                    'status' => 'failed',
                    'message' => 'method not found'
                ]);
            }
        } else {
            $MethodClass = new $ClassName();
            if (is_callable(array($MethodClass, $MethodName))) {
                return $MethodClass->$MethodName($vars);
            } else {
                return json_encode([
                    'status' => 'failed',
                    'message' => 'action not found'
                ]);
            }
        }
    }
}