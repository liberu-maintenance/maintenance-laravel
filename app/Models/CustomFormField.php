<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
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
])]
class CustomFormField extends Model
{
    use HasFactory;

    public function customForm(): BelongsTo
    {
        return $this->belongsTo(CustomForm::class);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function active($query)
    {
        return $query->where('is_active', true);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function ordered($query)
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
    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'is_active' => 'boolean',
            'options' => 'array',
            'validation_rules' => 'array',
            'order' => 'integer',
        ];
    }
}