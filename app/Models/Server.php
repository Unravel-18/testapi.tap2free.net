<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;

class Server extends Model
{
    protected $guarded = [];

    public static $rules = [
        'server' => 'required|unique:servers,server', 
        'name' => 'required', 
        'country' => 'required', 
        //'img_flag' => 'is_file:jpeg,jpg,png,gif', 
        //'img_map' => 'is_file:jpeg,jpg,png,gif', 
        'ip' => 'required|ip', 
        //'date' => 'required|date', 
    ];
    
    public function apis()
    {
    }
    
    public function ApiServers()
    {
        return $this->hasMany('App\Models\ApiServer', 'server_id', 'id');
    }
}
