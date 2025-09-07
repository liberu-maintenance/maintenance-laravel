<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    use HasFactory;

    protected $primaryKey = 'opportunity_id';

    protected $fillable = [
        'deal_size',
        'stage',
        'closing_date',
        'team_id',
    ];

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
