<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;

class DataFaq extends Model
{
    protected $guarded = [];

    public function language()
    {
        return $this->belongsTo('App\Models\Language');
    }
}
