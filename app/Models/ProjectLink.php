<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectLink extends Model
{
    protected $table = 'project_links';

    protected $fillable = [
        'project_id',
        'url',
        'type'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
