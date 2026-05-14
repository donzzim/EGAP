<?php

namespace App\Filament\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Livewire\Component;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        session()->flash('egap_boas_vindas', true);

        return redirect()->intended(filament()->getUrl());
    }
}
