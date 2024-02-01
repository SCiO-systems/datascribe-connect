<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'blocks';
    protected $casts = ['isGlobal' => 'boolean'];
    public $timestamps = true;

    /**
     * Changes the owner of the questionnaire.
     *
     * @param User $user
     * @return void
     */
    public function setOwner(User $user)
    {
        BlockUser::where('block_id', $this->id)
            ->where('user_id', $user->id)
            ->delete();

        BlockUser::where('block_id', $this->id)
            ->where('role', User::ROLE_OWNER)
            ->delete();

        $this->users()->attach($user->id, ['role' => User::ROLE_OWNER]);
    }

    /**
     * Adds a viewer or updates an existing user to viewer for a questionnaire.
     *
     * @param User $user
     * @return void
     */
    public function setViewer(User $user)
    {
        BlockUser::where('block_id', $this->id)
            ->where('user_id', $user->id)
            ->delete();

        $this->users()->attach($user->id, ['role' => User::ROLE_VIEWER]);
    }

    /**
     * Retrieves the role for a user.
     *
     * @param User $user
     * @return string
     */
    public function role(User $user)
    {
        return $this->users()->where('user_id', $user->id)->first()->pivot->role;
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'block_user',
            'block_id',
            'user_id'
        )->withTimestamps()->withPivot('block_user.role');
    }

    public function owner()
    {
        return $this->hasOneThrough(
            User::class,
            BlockUser::class,
            'block_id',
            'id',
            'id',
            'user_id'
        )->where('block_user.role', User::ROLE_OWNER);
    }
}
