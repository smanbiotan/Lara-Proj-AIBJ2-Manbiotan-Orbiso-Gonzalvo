<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Shared validation for every booking channel (web form, public API, chatbot tool). */
class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'name' => trim((string) $this->input('name')),
            'phone' => trim((string) $this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        return self::baseRules();
    }

    public static function baseRules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{7,30}$/'],
            'service' => ['required', 'string', Rule::exists('services', 'slug')->where('is_active', true)],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'submission_token' => ['nullable', 'string', 'max:64'],
        ];
    }

    public static function messagesList(): array
    {
        return [
            'phone.regex' => 'Please enter a valid contact number.',
            'service.exists' => 'Please choose an available service.',
            'appointment_date.after_or_equal' => 'Please choose today or a future date.',
        ];
    }

    public function messages(): array
    {
        return self::messagesList();
    }

    /** Converts validated input into the array BookingService expects. */
    public static function toBookingData(array $validated): array
    {
        $validated['service'] = Service::where('slug', $validated['service'])->firstOrFail();
        return $validated;
    }
}
