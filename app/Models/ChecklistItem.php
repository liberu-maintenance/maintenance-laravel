<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_id',
        'title',
        'description',
        'type',
        'required',
        'options',
        'order',
        'status',
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'order' => 'integer',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function scopeRequired($query)
    {
        return $query->where('required', true);
    }

    public function scopeOptional($query)
    {
        return $query->where('required', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}