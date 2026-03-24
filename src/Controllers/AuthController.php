<?php

namespace App\Controllers;

class AuthController
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = \App\Models\User::findByEmail($email);

            if ($user && $user->verifyPassword($password)) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_name'] = $user->name;
                $_SESSION['user_role'] = $user->role;

                header('Location: /quotes');
                exit;
            }

            $_SESSION['error'] = 'Email ou mot de passe incorrect';
            header('Location: /login');
            exit;
        }

        $this->renderLogin();
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /login');
        exit;
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $name = $_POST['name'] ?? '';

            if (empty($email) || empty($password) || empty($name)) {
                $_SESSION['error'] = 'Tous les champs sont obligatoires';
                header('Location: /register');
                exit;
            }

            if (\App\Models\User::findByEmail($email)) {
                $_SESSION['error'] = 'Cet email est déjà utilisé';
                header('Location: /register');
                exit;
            }

            $user = \App\Models\User::create([
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'name' => $name,
                'role' => 'user'
            ]);

            \App\Models\Company::create([
                'user_id' => $user->id,
                'name' => 'Mon Entreprise'
            ]);

            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->name;
            $_SESSION['user_role'] = $user->role;

            header('Location: /quotes');
            exit;
        }

        $this->renderRegister();
    }

    private function renderLogin(): void
    {
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        extract(compact('error'));
        include __DIR__ . '/../Views/auth/login.php';
    }

    private function renderRegister(): void
    {
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        extract(compact('error'));
        include __DIR__ . '/../Views/auth/register.php';
    }

    public static function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    public static function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUserName(): ?string
    {
        return $_SESSION['user_name'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['user_role'] ?? '') === 'admin';
    }
}
