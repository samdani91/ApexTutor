<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTuitionJobRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tuition_type'           => 'required|in:student_home,tutor_home,online',
            'medium'                 => 'required|in:bangla_medium,english_medium,english_version,madrasha,test_preparation',
            'tutoring_style'         => 'required|in:one_to_one,group,online',
            'district_id'            => 'required|exists:districts,id',
            'area_id'                => 'required|exists:areas,id',
            'address_details'        => 'required|string|max:500',
            'class_level'            => 'required|string|max:50',
            'subject_ids'            => 'required|array|min:1',
            'subject_ids.*'          => 'integer|exists:subjects,id',
            'student_gender'         => 'required|in:male,female,any',
            'num_students'           => 'required|integer|min:1|max:20',
            'tutor_gender_pref'      => 'required|in:male,female,any',
            'offered_salary'         => 'required|integer|min:500|max:100000',
            'hire_date'              => 'required|date|after_or_equal:today',
            'tutoring_time'          => 'required|date_format:H:i',
            'tutoring_days_per_week' => 'required|integer|min:1|max:7',
            'student_institute'      => 'required|string|max:255',
            'extra_requirements'     => 'nullable|string|max:1000',
        ];
    }
}
