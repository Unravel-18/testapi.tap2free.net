<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;

class Ip extends Model
{
    public function saveDB()
    {
        if ($this->connection) {
            $connection = $this->connection;
        } else {
            $connection = config('database.default');
        }
        if (is_null($this->count_error_short_code)) {
            $this->count_error_short_code = 0;
        }
        if (is_null($this->ip)) {
            $this->ip = "";
        }
        
        $this->save();
        
        foreach (config("db") as $db_key => $db_config) {
            if ($db_key == $connection) {
                continue;
            }
            
            if (isset($db_config["ip_save"]) && $db_config["ip_save"]) {
                if (DB::connection($db_key)->table('ips')->where('ip', "=", $this->ip)->count() > 0) {
                    DB::connection($db_key)->table('ips')->where('ip', "=", $this->ip)->update([
                        "count_error_short_code" => $this->count_error_short_code,
                        "first_error_short_code_at" => $this->first_error_short_code_at,

                        "updated_at" => date("Y-m-d H:i"),
                    ]);
                } else {
                    DB::connection($db_key)->table('ips')->insert([
                        [
                            "ip" => $this->ip,
                            "count_error_short_code" => $this->count_error_short_code,
                            "first_error_short_code_at" => $this->first_error_short_code_at,
                            
                            "created_at" => date("Y-m-d H:i"),
                        ],
                    ]);
                }
            }
        }
    }
}
