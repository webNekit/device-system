<?php

namespace App\Presentation\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Вход в систему')]
#[Layout('components.layouts.guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            // Если мастер - кидаем на доску, если кладовщик - на склад
            $user = Auth::user();
            if ($user->hasRole('Storekeeper')) {
                return $this->redirectRoute('inventory.products', navigate: true);
            }

            return $this->redirectRoute('tickets.index', navigate: true);
        }

        $this->addError('email', 'Неверный логин или пароль.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
