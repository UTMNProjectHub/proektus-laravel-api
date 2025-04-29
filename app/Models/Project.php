<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

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

    function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'projects_tags');
    }

    function links(): HasMany
    {
        return $this->hasMany(ProjectLink::class);
    }

    function urls(): HasMany
    {
        return $this->HasMany(ProjectURL::class, 'project_id', 'id');
    }

    function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
    }
}
