<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location:main.php");
    exit;
}
 
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // Redirect user to welcome page
                            header("location: main.php");
                        } else{
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    // Username doesn't exist, display a generic error message
                    $login_err = "Invalid username or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | PathIntel.ai</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;600&family=DM+Mono:wght@500&family=Sora:wght@300;400;600&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* THEME SYNC: Deep Space Navy */
        :root {
            --color-ink: #0A0E1A;
            --color-surface: #121826;
            --color-accent: #00D4FF;
            --color-accent-soft: rgba(0, 212, 255, 0.15);
            --color-error: #FF4D4D;
            --font-heading: 'Playfair Display', serif;
            --font-mono: 'DM Mono', monospace;
            --font-body: 'Sora', sans-serif;
            --transition-glide: all 400ms cubic-bezier(0.23, 1, 0.32, 1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: var(--font-body);
            background: var(--color-ink);
            height: 100vh;
            display: flex;
            overflow: hidden;
            color: #F8FAFC;
        }

        /* --- SPLIT LAYOUT --- */
        .split-container {
            display: flex;
            width: 100%;
            height: 100%;
        }

        /* LEFT PANEL */
        .panel-left {
            flex: 1.2;
            background: #070B14; /* Slightly darker than base */
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 80px;
            color: #FFFFFF;
            overflow: hidden;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* RIGHT PANEL */
        .panel-right {
            flex: 1;
            background: var(--color-ink);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px;
            position: relative;
        }

        @media (max-width: 992px) {
            body { overflow-y: auto; height: auto; }
            .split-container { flex-direction: column; }
            .panel-left { padding: 40px; min-height: 300px; flex: none; }
            .panel-right { padding: 40px; flex: none; }
        }

        /* --- DECORATIONS --- */
        .floating-ring {
            position: absolute;
            border: 1px solid rgba(0, 212, 255, 0.1);
            border-radius: 50%;
            animation: floatUp 12s infinite linear;
            z-index: 1;
        }
        @keyframes floatUp {
            from { transform: translateY(100%) rotate(0deg); opacity: 0; }
            50% { opacity: 0.12; }
            to { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }

        .branding { font-family: var(--font-heading); font-size: 1.8rem; letter-spacing: -1px; z-index: 2; color: var(--color-accent); font-weight: 700; }
        .quote { font-family: var(--font-heading); font-size: 3rem; font-weight: 300; line-height: 1.1; margin: auto 0; z-index: 2; max-width: 500px; color: #F8FAFC; }
        .testimonial { font-size: 0.8rem; color: rgba(255, 255, 255, 0.4); z-index: 2; text-transform: uppercase; letter-spacing: 0.1em; }

        /* --- FORM STYLING --- */
        .form-wrapper {
            width: 100%;
            max-width: 400px;
            animation: formFadeUp 600ms cubic-bezier(0.23, 1, 0.32, 1) forwards;
        }
        @keyframes formFadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-title { font-family: var(--font-heading); font-size: 3rem; font-weight: 700; margin-bottom: 40px; color: #F8FAFC; letter-spacing: -0.02em; }

        .input-group { margin-bottom: 24px; position: relative; opacity: 0; animation: fieldFadeIn 400ms forwards; }
        @keyframes fieldFadeIn { to { opacity: 1; } }

        .label {
            display: block;
            font-family: var(--font-mono);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #94A3B8;
            margin-bottom: 8px;
        }

        .input {
            width: 100%;
            height: 52px;
            padding: 14px 16px;
            background: var(--color-surface);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            font-family: var(--font-body);
            font-size: 15px;
            color: #F8FAFC;
            transition: var(--transition-glide);
        }

        .input::placeholder { color: rgba(255, 255, 255, 0.2); }
        .input:focus {
            outline: none;
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px var(--color-accent-soft);
        }

        .input.is-invalid { border-left: 3px solid var(--color-error); }
        .error-text { font-size: 11px; color: var(--color-error); margin-top: 6px; display: flex; align-items: center; gap: 4px; }

        .btn-submit {
            width: 100%;
            height: 52px;
            background: transparent;
            border: 1px solid var(--color-accent);
            border-radius: 4px;
            color: var(--color-accent);
            font-family: var(--font-body);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            cursor: pointer;
            transition: var(--transition-glide);
            margin-top: 16px;
        }
        .btn-submit:hover { 
            background: var(--color-accent);
            color: var(--color-ink);
            box-shadow: 0 0 20px var(--color-accent-soft);
        }
        .btn-submit:active { transform: scale(0.98); }

        .footer-links { margin-top: 32px; font-size: 13px; color: #94A3B8; display: flex; flex-direction: column; gap: 8px; }
        .footer-links a { color: var(--color-accent); text-decoration: none; transition: 0.2s; }
        .footer-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="split-container">
    <!-- LEFT PANEL: THE ATMOSPHERE -->
    <div class="panel-left">
        <!-- CSS Rings -->
        <div class="floating-ring" style="width: 200px; height: 200px; left: 10%; bottom: -10%; animation-duration: 15s;"></div>
        <div class="floating-ring" style="width: 120px; height: 120px; right: 20%; top: 20%; animation-duration: 10s; animation-delay: 2s;"></div>
        <div class="floating-ring" style="width: 300px; height: 300px; left: 40%; top: 10%; animation-duration: 18s; animation-delay: 5s;"></div>

        <div class="branding">PathIntel.ai</div>
        <div class="quote">"Knowledge is the only bridge to the future."</div>
        <div class="testimonial">Trusted by 10,000+ Students globally</div>
    </div>

    <!-- RIGHT PANEL: THE FOCUS -->
    <div class="panel-right">
        <div class="form-wrapper">
            <h2 class="form-title">Welcome Back.</h2>

            <?php if(!empty($login_err)): ?>
                <div style="background: rgba(255, 77, 77, 0.05); border-left: 3px solid var(--color-error); padding: 12px 16px; margin-bottom: 24px; font-size: 14px; color: var(--color-error);">
                    <?php echo $login_err; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <!-- Username -->
                <div class="input-group" style="animation-delay: 100ms;">
                    <label class="label">Username</label>
                    <input type="text" name="username" class="input <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" placeholder="Your university ID or email">
                    <?php if(!empty($username_err)): ?>
                        <span class="error-text"><i data-lucide="alert-circle" style="width: 12px;"></i> <?php echo $username_err; ?></span>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="input-group" style="animation-delay: 150ms;">
                    <label class="label">Password</label>
                    <input type="password" name="password" class="input <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="••••••••">
                    <?php if(!empty($password_err)): ?>
                        <span class="error-text"><i data-lucide="alert-circle" style="width: 12px;"></i> <?php echo $password_err; ?></span>
                    <?php endif; ?>
                    <a href="reset-password.php" style="position: absolute; top: 0; right: 0; font-size: 11px; color: rgba(0,0,0,0.4); text-decoration: none; font-family: var(--font-mono); text-transform: uppercase;">Forgot?</a>
                </div>

                <!-- Submit -->
                <div class="input-group" style="animation-delay: 200ms;">
                    <button type="submit" class="btn-submit" id="submitBtn">Sign In</button>
                </div>

                <div class="footer-links">
                    <span>New here? <a href="register.php" style="font-weight: 600;">Apply for an account</a></span>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Initialize Lucide Icons
    lucide.createIcons();

    // Loading State Simulation
    const form = document.querySelector('form');
    const btn = document.getElementById('submitBtn');
    form.onsubmit = () => {
        btn.innerHTML = '<span style="display: flex; gap: 4px; justify-content: center;"><span style="animation: pulse 1s infinite;">•</span><span style="animation: pulse 1s infinite 0.2s;">•</span><span style="animation: pulse 1s infinite 0.4s;">•</span></span>';
        btn.style.pointerEvents = 'none';
    };

    const style = document.createElement('style');
    style.innerHTML = '@keyframes pulse { 0%, 100% { opacity: 0.3; } 50% { opacity: 1; } }';
    document.head.appendChild(style);
</script>

</body>
</html>
