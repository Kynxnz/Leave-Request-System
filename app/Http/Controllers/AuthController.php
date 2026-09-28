<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }
    public function register(Request $request)
    {
        $data=$request->validate(['name'=>['required','string','max:120'],'email'=>['required','email','max:255','unique:users,email'],'password'=>['required','string','min:8','confirmed']]);
        $user=User::create([...$data,'role'=>'Staff','status'=>'active']);
        foreach (LeaveType::where('is_active',true)->where('tracks_balance',true)->get() as $type) {
            $user->leaveBalances()->create(['leave_type_id'=>$type->id,'year'=>now()->year,'allocated'=>$type->annual_allowance,'used'=>0]);
        }
        Auth::login($user); $request->session()->regenerate();
        return redirect()->route('dashboard')->with('success','Account created successfully. An Administrator must assign your Manager before you can submit leave.');
    }
    public function login(Request $request)
    {
        $data=$request->validate(['email'=>['required','email'],'password'=>['required']]);
        if (!Auth::attempt([...$data,'status'=>'active'],$request->boolean('remember'))) return back()->withErrors(['email'=>'The email or password is incorrect, or the account is inactive.'])->onlyInput('email');
        $request->session()->regenerate(); return redirect()->intended(route('dashboard'))->with('success','Welcome back.');
    }
    public function logout(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login'); }
    public function showForgot() { return view('auth.forgot-password'); }
    public function sendReset(Request $request)
    {
        $data=$request->validate(['email'=>['required','email']]);
        $status=Password::sendResetLink($data);
        return $status===Password::RESET_LINK_SENT ? back()->with('success','If the account exists, a password reset link has been sent.') : back()->withErrors(['email'=>'Unable to send a reset link right now.']);
    }
    public function showReset(Request $request, string $token) { return view('auth.reset-password',['token'=>$token,'email'=>$request->email]); }
    public function reset(Request $request)
    {
        $data=$request->validate(['token'=>['required'],'email'=>['required','email'],'password'=>['required','confirmed','min:8']]);
        $status=Password::reset($data,function(User $user,string $password){$user->forceFill(['password'=>$password,'remember_token'=>Str::random(60)])->save();});
        return $status===Password::PASSWORD_RESET ? redirect()->route('login')->with('success','Password reset successfully. You can now sign in.') : back()->withErrors(['email'=>'This reset link is invalid or expired.']);
    }
}
