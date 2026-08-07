<?php

namespace App\Filament\Auth;

class LoginResponse implements \Filament\Auth\Http\Responses\Contracts\LoginResponse
{
    public function toResponse($request)
    {
        session()->flash('egap_boas_vindas', true);

        return redirect()->intended(filament()->getUrl());
    }
}
