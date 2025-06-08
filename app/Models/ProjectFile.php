<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        's3_key',
        'original_filename',
    ];

    protected $table = 'files';

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getContentsAttribute(): ?string
    {
        return Storage::disk('project-files')->get($this->s3_key);
    }

    public function setContentsAttribute(string $contents): void
    {
        try {
            Storage::disk('project-files')->put($this->s3_key, $contents);
            $this->updateTimestamps();
        } catch (\Exception $e) {
            throw new \RuntimeException('Не удалось сохранить содержимое файла: ' . $e->getMessage());
        }
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            try {
                Storage::disk('project-files')->delete($model->s3_key);
            } catch (\Exception $e) {
                throw new \RuntimeException('Не удалось удалить файл: ' . $e->getMessage());
            }
        });
    }
}
