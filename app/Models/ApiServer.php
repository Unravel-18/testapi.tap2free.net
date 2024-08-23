<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;

class ApiServer extends Model
{
    protected $guarded = [];
    
    public function server()
    {
        return $this->hasOne('App\Models\Server', 'id', 'server_id');
    }
}
