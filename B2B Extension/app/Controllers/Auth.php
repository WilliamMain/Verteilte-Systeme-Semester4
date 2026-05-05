<?php

namespace App\Controllers;

class Auth extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function login()
    {
        // Check if already logged in
        if (session()->get('mitarbeiter_logged_in')) {
            return redirect()->to(base_url('mitarbeiter-dashboard'));
        }

        $data = [
            'error' => $this->request->getGet('error') ?? ''
        ];

        return view('auth/login', $data);
    }

    public function loginProcess()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Validation
        if (empty($username) || empty($password)) {
            return redirect()->to(base_url('login') . '?error=' . urlencode('Bitte geben Sie Benutzername und Passwort ein'));
        }

        try {
            // Get user from database
            $query = $this->db->query("SELECT `Mitarbeiter-Nr`, `Name`, `Passwort` FROM `mitarbeiter` WHERE `Name` = ?", [$username]);
            $user = $query->getRow();

            // User not found
            if (!$user) {
                return redirect()->to(base_url('login') . '?error=' . urlencode('Benutzername nicht gefunden'));
            }

            // Check password (comparing as integers like in your original code)
            if ((int)$password !== (int)$user->Passwort) {
                return redirect()->to(base_url('login') . '?error=' . urlencode('Falsches Passwort'));
            }

            // Set session data
            session()->set([
                'mitarbeiter_id' => $user->{'Mitarbeiter-Nr'},
                'mitarbeiter_name' => $user->Name,
                'mitarbeiter_logged_in' => true
            ]);

            // Login successful
            return redirect()->to(base_url('mitarbeiter-dashboard'));

        } catch (\Exception $e) {
            return redirect()->to(base_url('login') . '?error=' . urlencode($e->getMessage()));
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }
}