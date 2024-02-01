<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VocabularyUser extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'vocabulary_user';
}
