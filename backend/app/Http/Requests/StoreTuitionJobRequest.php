<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTuitionJobRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tuition_type'           => 'required|in:home,online,group,home_and_online',
            'district_id'            => 'required|exists:districts,id',
            'area_id'                => 'nullable|exists:areas,id',
            'address_details'        => 'nullable|string|max:500',
            'class_level'            => 'required|string|max:50',
            'subject_ids'            => 'required|array|min:1',
            'subject_ids.*'          => 'integer|exists:subjects,id',
            'student_gender'         => 'required|in:male,female,any',
            'num_students'           => 'required|integer|min:1|max:20',
            'tutor_gender_pref'      => 'required|in:male,female,any',
            'offered_salary'         => 'required|integer|min:500|max:100000',
            'hire_date'              => 'nullable|date|after_or_equal:today',
            'address_details'        => 'nullable|string|max:500',
            'tutoring_time'          => 'nullable|date_format:H:i',
            'tutoring_days_per_week' => 'nullable|integer|min:1|max:7',
            'student_institute'      => 'nullable|string|max:255',
            'extra_requirements'     => 'nullable|string|max:1000',
        ];
    }
}
