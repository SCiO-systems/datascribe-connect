<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    // The available identity providers.
    public const IDENTITY_PROVIDER_LOCAL = 'local';
    public const IDENTITY_PROVIDER_ORCID = 'orcid';
    public const IDENTITY_PROVIDER_AUTH0 = 'auth0';

    // The available roles for managing sharing capabilities.
    public const ROLE_VIEWER = 'viewer';
    public const ROLE_OWNER = 'owner';

    // The available system roles.
    public const SYSTEM_ROLE_USER = 'user';
    public const SYSTEM_ROLE_ADMIN = 'admin';

    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function questionnaires()
    {
        return $this->belongsToMany(
            Questionnaire::class,
            'questionnaire_user',
            'user_id',
            'questionnaire_id'
        )->withPivot('role');
    }

    public function vocabularies()
    {
        return $this->belongsToMany(
            Vocabulary::class,
            'vocabulary_user',
            'user_id',
            'vocabulary_id'
        )->withPivot('role');
    }

    public function blocks()
    {
        return $this->belongsToMany(
            Block::class,
            'block_user',
            'user_id',
            'block_id'
        )->withPivot('role');
    }
}
