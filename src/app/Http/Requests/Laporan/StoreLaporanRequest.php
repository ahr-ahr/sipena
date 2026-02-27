<?php

namespace App\Http\Requests\Laporan;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanRequest extends FormRequest
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

            'mapel_id' => [
                'nullable',
                'exists:mapel,id',
            ],

            'attachments'     => ['nullable', 'array'],
            'attachments.*'   => [
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    public function withValidator($validator)
{
    $validator->after(function ($validator) {

        $kategori = \App\Models\KategoriLaporan::find(
            $this->input('kategori_id')
        );

        if (!$kategori) return;

        // jika kategori akademik tapi mapel kosong
        if (
            $kategori->nama === 'Pengaduan Akademik'
            && !$this->input('mapel_id')
        ) {
            $validator->errors()->add(
                'mapel_id',
                'Mata pelajaran wajib dipilih untuk laporan akademik.'
            );
        }
    });
}
}
