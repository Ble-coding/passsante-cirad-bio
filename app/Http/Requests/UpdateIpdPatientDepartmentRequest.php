<?php

namespace App\Http\Requests;

use App\Models\IpdPatientDepartment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIpdPatientDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = IpdPatientDepartment::$rules;
        $rules['case_id'] = 'required|unique:ipd_patient_departments,id,'.$this->route('ipdPatientDepartment')->id;
        if($this->route('ipdPatientDepartment')->is_discharge == 1){
            $rules['bed_type_id'] = 'nullable';
            $rules['bed_id'] = 'nullable';
            $rules['patient_id'] = 'nullable';
            $rules['case_id'] = 'nullable';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'case_id.required' => __('messages.new_change.case_required'),
            'bed_id.required' => __('messages.new_change.bed_required'),
        ];
    }
}
