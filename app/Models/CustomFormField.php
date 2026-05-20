<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_form_id',
        'label',
        'name',
        'type',
        'required',
        'placeholder',
        'help_text',
        'options',
        'validation_rules',
        'order',
        'is_active',
    ];

    protected $casts = [
        'required' => 'boolean',
        'is_active' => 'boolean',
        'options' => 'array',
        'validation_rules' => 'array',
        'order' => 'integer',
    ];

    public function customForm(): BelongsTo
    {
        return $this->belongsTo(CustomForm::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function getValidationRulesString()
    {
        $rules = [];

        if ($this->required) {
            $rules[] = 'required';
        }

        if ($this->validation_rules) {
            $rules = array_merge($rules, $this->validation_rules);
        }

        return implode('|', $rules);
    }
}