<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;

class Api extends Model
{
    protected $guarded = [];

    public static $rules = [
        'name' => 'required', 
        'key' => 'required|alpha_num|unique:apis,key', 
        'img' => 'required|image', 
    ];
}
