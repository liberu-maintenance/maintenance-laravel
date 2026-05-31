<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'name',
    'last_name',
    'email',
    'phone_number',
    'team_id',
])]
class Contact extends Model
{
    use HasFactory;

    #[\Override]
    protected $primaryKey = 'contact_id';

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
