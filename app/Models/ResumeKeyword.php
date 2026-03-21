<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeKeyword extends Model
{
    protected $table = 'resume_keywords';

    protected $fillable = ['keyword', 'category'];

    public $timestamps = true;
}
