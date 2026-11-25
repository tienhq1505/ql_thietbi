<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ChangePassword extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    protected function rules(): array
    {
        return [
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|different:current_password|confirmed',
            'password_confirmation' => 'required|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có tối thiểu 8 ký tự.',
            'password.different' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'password_confirmation.required' => 'Vui lòng nhập lại mật khẩu mới.',
        ];
    }

    public function updatePassword(): void
    {
        $this->validate();

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('message', 'Đổi mật khẩu tài khoản thành công!');
    }

    public function render()
    {
        return view('livewire.profile.change-password')
            ->layout('components.layouts.app', ['title' => 'Đổi mật khẩu cá nhân - MEMS']);
    }
}
