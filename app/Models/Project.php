<?php

namespace App\Models;

use App\Services\FileService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->files()->each(function ($file) {
                $file->delete();
            });

            $model->urls()->each(function ($url) {
                $url->delete();
            });
        });
    }

    function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_participants')
            ->withPivot('role')
            ->withTimestamps();
    }

    function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'project_tags');
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
        if ($user->getRoleNames()->intersect(['admin', 'teacher'])->isNotEmpty()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('privacy', 'public')
                ->orWhereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        });

    }

    public function getReadmeAttribute(): ?string
    {
        $file = $this->files()->where('original_filename', 'README.md')->first();
        return $file ? $file->contents : null;
    }
}
