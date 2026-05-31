<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'name',
    'description',
    'category',
    'is_active',
    'is_public',
    'created_by',
    'settings',
    'team_id',
])]
class CustomForm extends Model
{
    use HasFactory;

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(CustomFormField::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(CustomFormSubmission::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function active($query)
    {
        return $query->where('is_active', true);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function public($query)
    {
        return $query->where('is_public', true);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function byCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function duplicate($name = null)
    {
        $newForm = $this->replicate();
        $newForm->name = $name ?? $this->name . ' (Copy)';
        $newForm->save();

        // Duplicate form fields
        foreach ($this->fields as $field) {
            $newField = $field->replicate();
            $newField->custom_form_id = $newForm->id;
            $newField->save();
        }

        return $newForm;
    }
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'settings' => 'array',
        ];
    }
}