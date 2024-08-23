<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;

class Googletoken extends Model
{
    protected $guarded = [];

    public static $rules = [];
    
    protected $connection = 'mysql_google_tokens';    
}
