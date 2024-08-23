<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;

class ApiFaq extends Model
{
    protected $guarded = [];
    
    public function faq()
    {
        return $this->hasOne('App\Models\Faq', 'id', 'faq_id');
    }
}
