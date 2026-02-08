<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminAttendanceRequest extends FormRequest
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

            'break1_in'  => ['nullable', 'date_format:H:i', 'required_with:break1_out'],
            'break1_out' => ['nullable', 'date_format:H:i', 'required_with:break1_in'],

            'break2_in'  => ['nullable', 'date_format:H:i', 'required_with:break2_out'],
            'break2_out' => ['nullable', 'date_format:H:i', 'required_with:break2_in'],

            'note' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in.required'  => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in.date_format'  => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',

            'break1_in.date_format'  => '休憩時間が不適切な値です',
            'break1_out.date_format' => '休憩時間が不適切な値です',
            'break2_in.date_format'  => '休憩時間が不適切な値です',
            'break2_out.date_format' => '休憩時間が不適切な値です',

            'break1_in.required_with'  => '休憩時間が不適切な値です',
            'break1_out.required_with' => '休憩時間が不適切な値です',
            'break2_in.required_with'  => '休憩時間が不適切な値です',
            'break2_out.required_with' => '休憩時間が不適切な値です',

            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $ci = $this->input('clock_in');
            $co = $this->input('clock_out');

            if ($ci && $co && $ci > $co) {
                $v->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $this->checkBreak($v, 'break1_in', 'break1_out', $ci, $co);
            $this->checkBreak($v, 'break2_in', 'break2_out', $ci, $co);
        });
    }

    private function checkBreak($v, string $binKey, string $boutKey, ?string $ci, ?string $co): void
    {
        $bin  = $this->input($binKey);
        $bout = $this->input($boutKey);

        if (!$bin && !$bout) return;

        if ($bin && $bout && $bin > $bout) {
            $v->errors()->add($boutKey, '休憩時間が不適切な値です');
        }

        if ($ci && $bin && $bin < $ci) {
            $v->errors()->add($binKey, '休憩時間が不適切な値です');
        }

        if ($co && $bin && $bin > $co) {
            $v->errors()->add($binKey, '休憩時間が不適切な値です');
        }

        if ($co && $bout && $bout > $co) {
            $v->errors()->add('clock_out', '休憩時間もしくは退勤時間が不適切な値です');
        }
    }
}
