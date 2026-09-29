<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Đăng nhập - MEMS Bệnh viện')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required|min:4',
    ];

    protected array $messages = [
        'email.required' => 'Vui lòng nhập địa chỉ email.',
        'email.email' => 'Email không đúng định dạng.',
        'password.required' => 'Vui lòng nhập mật khẩu.',
    ];

    public function login()
    {
        $this->validate();

        // Rate limiting: 5 lần/phút theo email + IP
        $throttleKey = Str::lower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Quá nhiều lần đăng nhập sai. Vui lòng thử lại sau {$seconds} giây.");

            return;
        }

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password, 'status' => 'active'], $this->remember)) {
            RateLimiter::clear($throttleKey);
            session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        RateLimiter::hit($throttleKey, 60); // 60 giây window
        $this->addError('email', 'Thông tin đăng nhập không chính xác hoặc tài khoản đã bị khóa.');
    }

    public function fillDemoAccount(string $email)
    {
        $this->email = $email;
        $this->password = 'password';
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
