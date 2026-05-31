<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'checklist_id',
    'title',
    'description',
    'type',
    'required',
    'options',
    'order',
    'status',
])]
class ChecklistItem extends Model
{
    use HasFactory;

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function required($query)
    {
        return $query->where('required', true);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function optional($query)
    {
        return $query->where('required', false);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function ordered($query)
    {
        return $query->orderBy('order');
    }
    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'options' => 'array',
            'order' => 'integer',
        ];
    }
}