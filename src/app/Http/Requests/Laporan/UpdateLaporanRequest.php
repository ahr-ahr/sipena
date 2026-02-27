<?php

namespace App\Http\Requests\Laporan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul'        => ['required', 'string', 'max:150'],
            'deskripsi'    => ['required', 'string'],
            'kategori_id'  => ['required', 'exists:kategori_laporan,id'],

            'attachments'     => ['nullable', 'array'],
            'attachments.*'   => [
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }
}
