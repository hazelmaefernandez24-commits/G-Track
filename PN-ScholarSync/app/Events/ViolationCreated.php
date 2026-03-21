<?php

namespace App\Events;

use App\Models\Violation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ViolationCreated
{
    use Dispatchable, SerializesModels;

    public $violation;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Violation  $violation
     * @return void
     */
    public function __construct(Violation $violation)
    {
        $this->violation = $violation;
    }


}
