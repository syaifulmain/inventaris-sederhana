<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    #[Validate('required|email')]
    public $email = '';
    #[Validate('required|string|min:6')]
    public $password = '';
    public $remember = false;

    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ];
    }

    public function login(AuthService $authService)
    {
        $this->validate();

        try {
            $result = $authService->login([
                'email' => $this->email,
                'password' => $this->password,
            ]);

            Auth::login($result['user'], $this->remember);

            if ($result['user']->isAdmin()) {
                return redirect()->route('admin.users');
            }

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            // Tampilkan error ke user
            $this->addError('login', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
