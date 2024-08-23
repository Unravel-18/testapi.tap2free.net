<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;

class Faq extends Model
{
    protected $guarded = [];
    
    public function data()
    {
        return $this->hasMany('App\Models\DataFaq');
    }
    
    public function getQuestionByLanguage($language)
    {
        foreach ($this->data as $dataFaq) {
            if ($dataFaq->language_id == $language->id) {
                return $dataFaq->question;
            }
        }
        
        return '';
    }
    
    public function getAnswersByLanguage($language)
    {
        foreach ($this->data as $dataFaq) {
            if ($dataFaq->language_id == $language->id) {
                return $dataFaq->answer;
            }
        }
        
        return '';
    }
}
