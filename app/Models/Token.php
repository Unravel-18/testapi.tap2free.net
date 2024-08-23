<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;

class Token extends Model
{
    protected $connection = 'mysql_keys';   
    
    public static function getUniqueAppId()
    {
        do {
            $key = str_random(20);
        } while (Token::where("app_id", "=", $key)->count() >= 1);
        
        return $key;
    }
    
    public static function getUniqueShortCode()
    {
        do {
            $key = strtolower(str_random(6));
        } while (Token::where("short_code", "=", $key)->count() >= 1);
        
        return $key;
    }
    
    public function activate()
    {
        if ($this->status == "not activated") {
            $this->status = "active";
            $this->activated_at = date("Y-m-d H:i");
            $this->app_id = self::getUniqueAppId();
            $this->saveDB();
        } elseif($this->isActive()) {
            $this->app_id = self::getUniqueAppId();
            $this->saveDB();
        }
    }
    
    public function getActiveSeconds()
    {
        if ($this->subscribe == "1") {
            if ($this->status == "active" && $this->days > 0 && $this->activated_at && $this->subscribe_time) {
                return $this->subscribe_time - time();
            }
        } else {
            if ($this->status == "active" && $this->days > 0 && $this->activated_at) {
                $dt = date_create($this->activated_at);
            
                if ($dt) {
                    $seconds = ($this->days * 60 * 60 * 24) - (time() - $dt->getTimestamp());
                
                    return $seconds;
                }
            }
        }
        
        return null;
    }
    
    public function isActive()
    {
        $seconds = $this->getActiveSeconds();
        
        if ($this->status == "active") {
            if (true) {
                if ($this->subscribe == "1") {
                    if ($this->subscribe_time > time()) {
                        return true;
                    } elseif($this->status != "expired") {
                        $this->status = "expired";
                        $this->saveDB();
                    }
                } else {
                    if ($seconds > 0) {
                        return true;
                    } elseif($this->status != "expired") {
                        $this->status = "expired";
                        $this->saveDB();
                    }
                }
            }
        }
        
        return false;
    }
    
    public function getLeft($is_str = true)
    {
        $seconds = $this->getActiveSeconds();
        
        if ($seconds) {
            if (true) {
                if ($seconds < 0) {
                    return ($is_str ? '-' : 0);
                } elseif ($seconds < 60) {
                    return ($is_str ? ($seconds . " сек.") : $seconds);
                } elseif ($seconds < 60*60) {
                    return ($is_str ? (ceil($seconds/60) . " мин.") : $seconds);
                } elseif ($seconds < 60*60*24) {
                    return ($is_str ? (ceil($seconds/(60*60)) . " час.") : $seconds);
                } else {
                    return ($is_str ? (ceil($seconds/(60*60*24)) . " дн.") : $seconds);
                }
            }
        }
    }
    
    public function getDateActive()
    {
        $seconds = $this->getActiveSeconds();
        
        if ($seconds) {
            if (true) {
                if ($this->subscribe == "1") {
                    if ($this->subscribe_time) {
                        return date("Y-m-d H:i", $this->subscribe_time);
                    }
                }
                
                if ($this->activated_at) {
                    $dt = date_create($this->activated_at);
                
                    if ($dt && $seconds > 0) {
                        return date("Y-m-d H:i", $dt->getTimestamp() + $seconds);
                    }
                }
            }
        }
        
        return null;
    }
    
    public function saveDB()
    {
        if ($this->connection) {
            $connection = $this->connection;
        } else {
            $connection = config('database.default');
        }
        
        if (is_null($this->short_code)) {
            $this->short_code = Token::getUniqueShortCode();
        }
        if (is_null($this->status)) {
            $this->status = "not activated";
        }
        if (is_null($this->reset_code_24hour_count)) {
            $this->reset_code_24hour_count = 0;
        }
        if (is_null($this->app_hour_count)) {
            $this->app_hour_count = 0;
        }
        if (is_null($this->app_24hour_count)) {
            $this->app_24hour_count = 0;
        }
        
        //$this->save();
        
        foreach (config("db") as $db_key => $db_config) {
            if ($db_key == $connection) {
                //continue;
            }
            
            if (isset($db_config["token_save"]) && $db_config["token_save"]) {
                if (DB::connection($db_key)->table('tokens')->where('short_code', "=", $this->short_code)->count() > 0) {
                    DB::connection($db_key)->table('tokens')->where('short_code', "=", $this->short_code)->update([
                        "app_id" => $this->app_id,
                        "status" => $this->status,
                        "days" => $this->days,
                        "email" => $this->email,
                        "reset_code_24hour_count" => $this->reset_code_24hour_count,
                        "app_hour_count" => $this->app_hour_count,
                        "app_24hour_count" => $this->app_24hour_count,
                        "activated_at" => $this->activated_at,
                        "reset_code_24hour_at" => $this->reset_code_24hour_at,
                        "app_hour_at" => $this->app_hour_at,
                        "app_24hour_at" => $this->app_24hour_at,
                        "subscribe" => $this->subscribe,
                        "subscribe_time" => $this->subscribe_time,
                        "updated_at" => date("Y-m-d H:i"),
                    ]);
                } else {
                    DB::connection($db_key)->table('tokens')->insert([
                        [
                            "short_code" => $this->short_code,
                            "app_id" => $this->app_id,
                            "status" => $this->status,
                            "days" => $this->days,
                            "email" => $this->email,
                            "reset_code_24hour_count" => $this->reset_code_24hour_count,
                            "app_hour_count" => $this->app_hour_count,
                            "app_24hour_count" => $this->app_24hour_count,
                            "activated_at" => $this->activated_at,
                            "reset_code_24hour_at" => $this->reset_code_24hour_at,
                            "app_hour_at" => $this->app_hour_at,
                            "app_24hour_at" => $this->app_24hour_at,
                            "subscribe" => $this->subscribe,
                            "subscribe_time" => $this->subscribe_time,
                            "created_at" => date("Y-m-d H:i"),
                        ],
                    ]);
                }
            }
        }
    }
    
    public function deleteDB()
    {
        if ($this->connection) {
            $connection = $this->connection;
        } else {
            $connection = config('database.default');
        }
        
        foreach (config("db") as $db_key => $db_config) {
            if ($db_key == $connection) {
                //continue;
            }
            
            if (isset($db_config["token_save"]) && $db_config["token_save"]) {
                DB::connection($db_key)->table('tokens')->where("short_code", "=", $this->short_code)->delete();
            }
        }
                        
        //$this->delete();                
    }
}
