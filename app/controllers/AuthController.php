<?php
class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('auth/login', ['error' => $this->flash('error')], null);
    }

    public function login(): void
    {
        $input = $this->input();
        Csrf::guard($input);

        $v = new Validator($input);
        $v->required('email')->email('email')->required('password');

        if ($v->fails()) {
            $this->flash('error', $v->firstError());
            $this->redirect('/login');
        }

        $ok = Auth::attempt($input['email'], $input['password']);

        if (!$ok) {
            ActivityLogger::log('login_failed', "Failed login attempt for {$input['email']}");
            $this->flash('error', 'Invalid email or password, or your account is inactive.');
            $this->redirect('/login');
        }

        ActivityLogger::log('login', 'User logged in');

        $home = match (Auth::role()) {
            'admin'    => '/admin/dashboard',
            'agent'    => '/agent/dashboard',
            'customer' => '/customer/dashboard',
        };
        $this->redirect($home);
    }

    public function logout(): void
    {
        ActivityLogger::log('logout', 'User logged out');
        Auth::logout();
        $this->redirect('/login');
    }
}
