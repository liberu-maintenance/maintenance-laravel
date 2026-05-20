<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPerformanceEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'vendor_contract_id',
        'work_order_id',
        'evaluation_date',
        'evaluated_by',
        'quality_rating',
        'timeliness_rating',
        'communication_rating',
        'cost_effectiveness_rating',
        'professionalism_rating',
        'overall_rating',
        'strengths',
        'areas_for_improvement',
        'comments',
        'would_recommend',
        'team_id',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'overall_rating' => 'decimal:2',
        'would_recommend' => 'boolean',
        'quality_rating' => 'integer',
        'timeliness_rating' => 'integer',
        'communication_rating' => 'integer',
        'cost_effectiveness_rating' => 'integer',
        'professionalism_rating' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($evaluation) {
            $evaluation->calculateOverallRating();
        });
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'vendor_id', 'company_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(VendorContract::class, 'vendor_contract_id');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function calculateOverallRating(): void
    {
        $ratings = [
            $this->quality_rating,
            $this->timeliness_rating,
            $this->communication_rating,
            $this->cost_effectiveness_rating,
            $this->professionalism_rating,
        ];

        $validRatings = array_filter($ratings, fn($rating) => $rating > 0);
        
        if (count($validRatings) > 0) {
            $this->overall_rating = round(array_sum($validRatings) / count($validRatings), 2);
        } else {
            $this->overall_rating = 0.00;
        }
    }

    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeHighPerformance($query, $threshold = 4.0)
    {
        return $query->where('overall_rating', '>=', $threshold);
    }

    public function scopeLowPerformance($query, $threshold = 3.0)
    {
        return $query->where('overall_rating', '<', $threshold);
    }
}
