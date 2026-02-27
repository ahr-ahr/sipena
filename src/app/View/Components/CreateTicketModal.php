<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Collection;

class CreateTicketModal extends Component
{
    public Collection $ticketCategories;

    public function __construct($ticketCategories = null)
    {
        $this->ticketCategories = collect($ticketCategories);
    }

    public function render()
    {
        return view('components.create-ticket-modal');
    }
}
