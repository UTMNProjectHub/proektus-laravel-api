<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'logo',
        'cover',
    ];

    function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_participants')
            ->withPivot('role')
            ->withTimestamps();
    }
}
