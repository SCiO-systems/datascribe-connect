<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireUser extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'questionnaire_user';
}
