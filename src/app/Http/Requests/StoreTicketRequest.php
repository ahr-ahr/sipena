<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'laporan_id' => ['required', 'exists:laporan,id'],

        'priority'    => ['required', 'in:low,medium,high,urgent'],
        'title'       => ['required', 'string', 'max:255'],
        'description' => ['required', 'string', 'min:10'],

        'attachments'   => ['nullable', 'array', 'max:5'],
        'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:2048'],

        'assigned_to' => ['nullable', 'exists:users,id'],
        'external_vendor' => ['nullable', 'string', 'max:150'],
        'external_notes' => ['nullable', 'string'],
    ];
}
}
