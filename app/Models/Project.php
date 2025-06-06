<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory, softDeletes;

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

    function urls(): HasMany
    {
        return $this->HasMany(ProjectURL::class, 'project_id', 'id');
    }

    function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function scopeVisible($query, User $user)
    {
        return $query->where(function ($subQuery) use ($user) {
            $subQuery->where('privacy', 'public')
                ->orWhereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->orWhere(function ($q) use ($user) {
                    $q->whereHas('users', function ($subQuery) use ($user) {
                        $subQuery->where('role', 'admin')
                            ->where('user_id', $user->id);
                    });
                });
        });
    }
}
