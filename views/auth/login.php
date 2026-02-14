<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Tabulation System</title>

    <!-- Inter Font -->
    <link rel="stylesheet" href="/tabulation/public/assets/css/inter-font.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/tabulation/public/assets/css/font-awesome.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0a0a0a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 440px;
        }
        
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 48px 40px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-logo h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #0a0a0a;
            letter-spacing: -0.03em;
            margin-bottom: 8px;
        }
        
        .login-logo p {
            font-size: 0.9375rem;
            color: #666;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #0a0a0a;
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            font-size: 0.9375rem;
            transition: all 0.2s;
            font-family: inherit;
            background: #ffffff;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #00d9ff;
            box-shadow: 0 0 0 3px rgba(0,217,255,0.1);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 1;
        }
        
        .input-group .form-control {
            padding-left: 44px;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #0a0a0a;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            letter-spacing: -0.01em;
        }
        
        .btn-login:hover {
            background: #1a1a1a;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(10,10,10,0.2);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.875rem;
        }
        
        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .alert ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e5e5;
        }
        
        .login-footer small {
            color: #999;
            font-size: 0.8125rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <h1>Tabulation</h1>
                <p>Sign in to your account</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/tabulation/login" method="post">
                <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-group">
                        <i class="fas fa-user input-group-icon"></i>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="Enter your username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                               required 
                               autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-group-icon"></i>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password"
                               required>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    Sign In
                </button>
            </form>
            
            <div class="login-footer">
                <small>Default: admin / admin123</small>
            </div>
        </div>
    </div>
</body>
</html>
