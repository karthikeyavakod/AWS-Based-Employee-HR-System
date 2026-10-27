<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\ChangePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();

        return view('employee.change-password', compact('user'));
    }

    public function update(ChangePasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if (! Hash::check($request->validated('current_password'), $user->password)) {
            return back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->validated('password')),
            'must_change_password' => false,
        ]);

        return redirect()
            ->route('employee.dashboard')
            ->with('success', 'Your password has been updated successfully.');
    }
}