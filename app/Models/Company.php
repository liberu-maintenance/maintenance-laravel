<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $primaryKey = 'company_id';

    protected $fillable = [
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
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    
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

    public function scopeSuppliers($query)
    {
        return $query->whereIn('type', ['supplier', 'both']);
    }

    public function scopeCustomers($query)
    {
        return $query->whereIn('type', ['customer', 'both']);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isSupplier(): bool
    {
        return in_array($this->type, ['supplier', 'both']);
    }

    public function isCustomer(): bool
    {
        return in_array($this->type, ['customer', 'both']);
    }
}
