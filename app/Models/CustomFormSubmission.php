<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'custom_form_id',
    'submitted_by',
    'guest_name',
    'guest_email',
    'data',
    'status',
    'reviewed_by',
    'reviewed_at',
    'notes',
])]
class CustomFormSubmission extends Model
{
    use HasFactory;

    public function customForm(): BelongsTo
    {
        return $this->belongsTo(CustomForm::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function pending($query)
    {
        return $query->where('status', 'pending');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function approved($query)
    {
        return $query->where('status', 'approved');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function rejected($query)
    {
        return $query->where('status', 'rejected');
    }
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }
}