<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'name',
    'address',
    'city',
    'state',
    'zip',
    'phone_number',
    'website',
    'industry',
    'description',
    'type',
    'contact_person',
    'email',
    'payment_terms',
    'is_active',
    'team_id',
])]
class Company extends Model
{
    use HasFactory;

    #[\Override]
    protected $primaryKey = 'company_id';
    
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function inventoryParts(): HasMany
    {
        return $this->hasMany(InventoryPart::class, 'supplier_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'customer_id');
    }

    public function vendorContracts(): HasMany
    {
        return $this->hasMany(VendorContract::class, 'vendor_id', 'company_id');
    }

    public function vendorPerformanceEvaluations(): HasMany
    {
        return $this->hasMany(VendorPerformanceEvaluation::class, 'vendor_id', 'company_id');
    }

    public function vendorWorkOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'vendor_id', 'company_id');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function suppliers($query)
    {
        return $query->whereIn('type', ['supplier', 'both']);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function customers($query)
    {
        return $query->whereIn('type', ['customer', 'both']);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function vendors($query)
    {
        return $query->whereIn('type', ['vendor', 'supplier', 'both']);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function active($query)
    {
        return $query->where('is_active', true);
    }

    public function isSupplier(): bool
    {
        return in_array($this->type, ['supplier', 'both', 'vendor']);
    }

    public function isCustomer(): bool
    {
        return in_array($this->type, ['customer', 'both']);
    }

    public function isVendor(): bool
    {
        return in_array($this->type, ['vendor', 'supplier', 'both']);
    }

    public function getAveragePerformanceRating(): float
    {
        return $this->vendorPerformanceEvaluations()
            ->avg('overall_rating') ?? 0.0;
    }

    public function getActiveContractsCount(): int
    {
        return $this->vendorContracts()
            ->where('status', 'active')
            ->count();
    }
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
