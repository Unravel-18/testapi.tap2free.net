<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;

class Android extends Model
{
    protected $guarded = [];

    public static $rules = [
    ];
    
    public function getMinutesActiveLevel($api_id)
    {
        if ($this->set_level_at && \App\Helpers\Setting::value('reset_level:'.$api_id)) {
            return  round((int)\App\Helpers\Setting::value('reset_level:'.$api_id) - ((time() - date_create($this->set_level_at)->getTimestamp()) / 60));
        } else {
            return null;
        }
    }
    
    public function invite_activ()
    {
        if ($this->created_at && $dt = date_create($this->created_at)) {
            if((time() - $dt->getTimestamp()) > (72 * 3600)){
                $this->invite_activ = 1;
                
                $this->save();
            }
        }
        
        return $this->invite_activ;
    }
}
