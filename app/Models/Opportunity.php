<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'deal_size',
    'stage',
    'closing_date',
    'team_id',
])]
class Opportunity extends Model
{
    use HasFactory;

    #[\Override]
    protected $primaryKey = 'opportunity_id';

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
