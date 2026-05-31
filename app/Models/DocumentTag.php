<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'name',
    'slug',
    'description',
    'color',
    'team_id',
])]
class DocumentTag extends Model
{
    use HasFactory;

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = static::generateUniqueSlug($tag->name, $tag->team_id);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name')) {
                $tag->slug = static::generateUniqueSlug($tag->name, $tag->team_id, $tag->id);
            }
        });
    }

    /**
     * Generate a unique slug for the tag within the team.
     */
    protected static function generateUniqueSlug(string $name, ?int $teamId, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::slugExists($slug, $teamId, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug exists within the team.
     */
    protected static function slugExists(string $slug, ?int $teamId, ?int $excludeId = null): bool
    {
        $query = static::where('slug', $slug)->where('team_id', $teamId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get the team that owns the tag.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get all documents with this tag.
     */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_tag');
    }

    /**
     * Scope a query to filter by team.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function forTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }
}
