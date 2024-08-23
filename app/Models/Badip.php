<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;

class Badip extends Model
{
    public function saveDB()
    {
        if ($this->connection) {
            $connection = $this->connection;
        } else {
            $connection = config('database.default');
        }
        if (is_null($this->status)) {
            $this->status = 1;
        }
        if (is_null($this->ip)) {
            $this->ip = "";
        }
        
        $this->save();  
                        
        foreach (config("db") as $db_key => $db_config) {
            if ($db_key == $connection) {
                continue;
            }
            
            if (isset($db_config["badip_save"]) && $db_config["badip_save"]) {
                if (DB::connection($db_key)->table('badips')->where('ip', "=", $this->ip)->count() > 0) {
                    DB::connection($db_key)->table('badips')->where('ip', "=", $this->ip)->update([
                        "status" => $this->status,
                            
                        "updated_at" => date("Y-m-d H:i"),
                    ]);
                } else {
                    DB::connection($db_key)->table('badips')->insert([
                        [
                            "ip" => $this->ip,
                            "status" => $this->status,
                            
                            "created_at" => date("Y-m-d H:i"),
                        ],
                    ]);
                }
            }
        }      
    }        
}
