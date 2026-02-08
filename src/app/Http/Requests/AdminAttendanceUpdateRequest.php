<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in'  => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],

            'break_start_1' => ['nullable', 'date_format:H:i'],
            'break_end_1'   => ['nullable', 'date_format:H:i'],

            'break_start_2' => ['nullable', 'date_format:H:i'],
            'break_end_2'   => ['nullable', 'date_format:H:i'],

            'note' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $in  = $this->input('clock_in');
            $out = $this->input('clock_out');

            if ($in && $out && $out < $in) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
                return;
            }

            $checkBreak = function ($bs, $be, $keyEnd) use ($validator, $in, $out) {

                if (($bs && !$be) || (!$bs && $be)) {
                    $validator->errors()->add($keyEnd, '休憩時間が不適切な値です');
                    return;
                }

                if (!$bs || !$be) return;

                if ($be < $bs) {
                    $validator->errors()->add($keyEnd, '休憩時間が不適切な値です');
                    return;
                }

                if (($in && $bs < $in) || ($out && $bs > $out) || ($in && $be < $in) || ($out && $be > $out)) {
                    $validator->errors()->add($keyEnd, '休憩時間が不適切な値です');
                }
            };

            $checkBreak($this->input('break_start_1'), $this->input('break_end_1'), 'break_end_1');
            $checkBreak($this->input('break_start_2'), $this->input('break_end_2'), 'break_end_2');
        });
    }
}
