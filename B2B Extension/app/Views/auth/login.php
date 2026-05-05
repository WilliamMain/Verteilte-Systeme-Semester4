<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitarbeiter Login - Marko Marko B2B</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #4a4a6a 0%, #3a3a5a 100%);
            color: white;
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 25px 20px;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h1 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
        }

        .nav-item {
            margin: 5px 0;
        }

        .nav-link {
            display: block;
            padding: 15px 25px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            border-left-color: #007bff;
            color: white;
        }

        .nav-link.active {
            background: rgba(255,255,255,0.15);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            width: 100%;
            max-width: 500px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 40px;
            text-align: center;
        }

        /* Login Container */
        .login-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }

        /* Error Message */
        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            text-align: center;
        }

        /* Form Styles */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-group input {
            padding: 15px 18px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .form-group input:hover {
            border-color: #007bff;
            background: white;
        }

        /* Login Button */
        .login-button {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            padding: 18px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
        }

        /* Loading state */
        .login-button.loading {
            opacity: 0.7;
            pointer-events: none;
        }

        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }

            .login-container {
                padding: 30px 25px;
            }

            .page-title {
                font-size: 24px;
                margin-bottom: 30px;
            }
        }

        /* Animation */
        .login-container {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

       
        .form-group input::placeholder {
            color: #adb5bd;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border-radius: 15px 15px 0 0;
        }

        .login-container {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h1>Marko Marko B2B</h1>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?= base_url('/') ?>" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('cart') ?>" class="nav-link">Warenkorb</a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('login') ?>" class="nav-link active">Mitarbeiter</a>
                </li>
                <li class="nav-item">
                    <a href="http://localhost/inventory/WilliyRollerB2C/html/index.html" class="nav-link">Zum B2C Shop ↗</a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="login-wrapper">
                <h1 class="page-title">Mitarbeiter Login</h1>
                
                <div class="login-container">
                    <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <?= esc($error) ?>
                    </div>
                    <?php endif; ?>
                    
                    <form action="<?= base_url('auth/loginProcess') ?>" method="post" class="login-form" id="login-form">
                        <div class="form-group">
                            <label for="username">Benutzername:</label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   required 
                                   placeholder="Geben Sie Ihren Benutzernamen ein"
                                   autocomplete="username">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Passwort:</label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   placeholder="Geben Sie Ihr Passwort ein"
                                   autocomplete="current-password">
                        </div>
                        
                        <button type="submit" class="login-button" id="login-btn">
                            Anmelden
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function() {
            const btn = document.getElementById('login-btn');
            btn.classList.add('loading');
            btn.textContent = 'Wird angemeldet...';
        });
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();

            document.getElementById('password').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('login-form').submit();
                }
            });
        });
    </script>
</body>
</html>