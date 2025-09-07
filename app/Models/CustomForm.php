<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'is_active',
        'is_public',
        'created_by',
        'settings',
        'team_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'settings' => 'array',
    ];

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByCategory($query, $category)
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
}