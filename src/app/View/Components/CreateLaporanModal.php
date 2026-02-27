<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Collection;

class CreateLaporanModal extends Component
{
    public Collection $laporanCategories;

    public function __construct($laporanCategories = null)
    {
        $this->laporanCategories = collect($laporanCategories);
    }

    public function render()
    {
        return view('components.create-laporan-modal');
    }
}
