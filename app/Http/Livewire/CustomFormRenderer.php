<?php

namespace App\Http\Livewire;

use Exception;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CustomForm;
use App\Models\CustomFormSubmission;
use Illuminate\Support\Facades\Validator;

class CustomFormRenderer extends Component
{
    use WithFileUploads;

    public $form;
    public $formData = [];
    public $guestName = '';
    public $guestEmail = '';
    public $submitted = false;
    public $errors = [];

    public function mount(CustomForm $form)
    {
        $this->form = $form->load('fields');

        // Initialize form data
        foreach ($this->form->fields as $field) {
            $this->formData[$field->name] = '';
        }
    }

    public function submit()
    {
        $this->validate();

        try {
            $submission = CustomFormSubmission::create([
                'custom_form_id' => $this->form->id,
                'submitted_by' => auth()->id(),
                'guest_name' => $this->guestName,
                'guest_email' => $this->guestEmail,
                'data' => $this->formData,
                'status' => 'pending',
            ]);

            $this->submitted = true;
            $this->emit('formSubmitted', $submission->id);

        } catch (Exception $e) {
            $this->addError('submission', 'There was an error submitting your form. Please try again.');
        }
    }

    public function rules()
    {
        $rules = [];

        if (!auth()->check()) {
            $rules['guestName'] = 'required|string|max:255';
            $rules['guestEmail'] = 'required|email|max:255';
        }

        foreach ($this->form->fields as $field) {
            $fieldRules = [];

            if ($field->required) {
                $fieldRules[] = 'required';
            }

            switch ($field->type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'file':
                    $fieldRules[] = 'file|max:10240'; // 10MB max
                    break;
            }

            if (!empty($fieldRules)) {
                $rules["formData.{$field->name}"] = implode('|', $fieldRules);
            }
        }

        return $rules;
    }

    public function validationAttributes()
    {
        $attributes = [
            'guestName' => 'Name',
            'guestEmail' => 'Email',
        ];

        foreach ($this->form->fields as $field) {
            $attributes["formData.{$field->name}"] = $field->label;
        }

        return $attributes;
    }

    public function render()
    {
        return view('livewire.custom-form-renderer');
    }
}