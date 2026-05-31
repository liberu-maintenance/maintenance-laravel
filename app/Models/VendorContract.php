<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'vendor_id',
    'contract_number',
    'title',
    'description',
    'contract_type',
    'start_date',
    'end_date',
    'contract_value',
    'currency',
    'status',
    'terms_and_conditions',
    'payment_frequency',
    'renewal_period_months',
    'auto_renewal',
    'renewal_date',
    'notes',
    'team_id',
])]
class VendorContract extends Model
{
    use HasFactory;

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'vendor_id', 'company_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function performanceEvaluations(): HasMany
    {
        return $this->hasMany(VendorPerformanceEvaluation::class);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function active($query)
    {
        return $query->where('status', 'active');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function expiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function expired($query)
    {
        return $query->where('end_date', '<', now())
            ->whereIn('status', ['active', 'expired']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    public function isExpiringSoon($days = 30): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        return $this->end_date->lte(now()->addDays($days));
    }

    public function getDaysUntilExpiration(): int
    {
        return max(0, now()->diffInDays($this->end_date, false));
    }
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'renewal_date' => 'date',
            'contract_value' => 'decimal:2',
            'auto_renewal' => 'boolean',
        ];
    }
}
