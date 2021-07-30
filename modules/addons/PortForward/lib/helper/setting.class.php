<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;
class setting
{
    private $Settings = [];
    private $SettingsFields = [];
    private $SettingsValues = [];
    public $SettingsTable = "";

    public function __construct($values){
        $this->SettingsFields = $values;
        $this->rebuildSettings($values);
    }

    private function rebuildSettings($values){
        foreach($values as $k => $v){
            if (!isset($v["options"])){
                $this->Settings[$k] = $v;
                continue;
            }
            $this->rebuildSettings($v["options"]);
        }
    }

    public function getSettings(){
        return $this->Settings;
    }

    private function getOptionsWithValue($options){
        foreach ($options as $Key => $Value){
            if (isset($Value["options"])){
                $options[$Key]["options"] = $this->getOptionsWithValue($options[$Key]["options"]);
                continue;
            }

            foreach ($this->SettingsValues as $SettingValue){
                if ($Key == $SettingValue->name){
                    $options[$Key]["value"] = $SettingValue->value;
                    break;
                }
            }
        }
        return $options;
    }

    public function getSettingsWithValue(){
        $this->SettingsValues = Capsule::table($this->SettingsTable)->get();
        return $this->getOptionsWithValue($this->SettingsFields);
    }

    public function verifySettings($Data){
        $Settings = $this->getSettings();
        if (count(array_diff_key($Settings, $Data)) != 0){
            return ["result" => "error" , "error" => "设置项不匹配"];
        }

        foreach($Data as $Key => $Value){
            if (isset($Settings[$Key]["php_filter"])){
                if (filter_var($Value, $Settings[$Key]["php_filter"]) === false) {
                    return [ "result" => "error", "error" => "{$Settings[$Key]["displayname"]} 填写错误, 请检查" ];
                }
            }
            if (isset($Settings[$Key]["filter"])){
                if (!$Settings[$Key]["filter"]($Value)){
                    return [ "result" => "error", "error" => "{$Settings[$Key]["displayname"]} 填写错误, 请检查" ];
                }
            }
            if (isset($Settings[$Key]["require"])){
                if (empty($Value)){
                    return [ "result" => "error", "error" => "{$Settings[$Key]["displayname"]} 为必填项, 请检查" ];
                }
            }
        }

        return true;
    }

    public function updateSettings($Data){
        $SettingKeys = array_keys($this->getSettings());
        foreach($SettingKeys as $Key){
            $SettingExists = Capsule::table($this->SettingsTable)->where("name", $Key)->exists();

            if (isset($Data[$Key])){
                if ($SettingExists) {
                    Capsule::table($this->SettingsTable)->where("name", $Key)->update([ "value" => $Data[$Key]]);
                } else {
                    Capsule::table($this->SettingsTable)->insert([
                        "name" => $Key,
                        "value" => $Data[$Key]
                    ]);
                }
            } elseif ($SettingExists) {
                Capsule::table($this->SettingsTable)->where("name", $Key)->delete();
            }
        }
    }
}
