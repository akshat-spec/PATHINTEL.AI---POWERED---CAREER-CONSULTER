<?php
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have atleast 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);
            
            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to login page
                header("location: login.php");
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
    <title>Register | PathIntel.ai</title>
    
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

        .split-container { display: flex; width: 100%; height: 100%; }

        .panel-left {
            flex: 1.2;
            background: #070B14;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 80px;
            color: #FFFFFF;
            overflow: hidden;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .panel-right {
            flex: 1;
            background: var(--color-ink);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px;
            position: relative;
            overflow-y: auto;
        }

        @media (max-width: 992px) {
            body { overflow-y: auto; height: auto; }
            .split-container { flex-direction: column; }
            .panel-left { padding: 40px; min-height: 300px; flex: none; }
            .panel-right { padding: 40px; flex: none; }
        }

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

        .input-group { margin-bottom: 20px; position: relative; opacity: 0; animation: fieldFadeIn 400ms forwards; }
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
            height: 48px;
            padding: 12px 16px;
            background: var(--color-surface);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            font-family: var(--font-body);
            font-size: 14px;
            color: #F8FAFC;
            transition: var(--transition-glide);
        }

        .input:focus {
            outline: none;
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px var(--color-accent-soft);
        }

        .input.is-invalid { border-left: 3px solid var(--color-error); }
        .error-text { font-size: 10px; color: var(--color-error); margin-top: 4px; display: flex; align-items: center; gap: 4px; }

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
            margin-top: 24px;
        }
        .btn-submit:hover { 
            background: var(--color-accent);
            color: var(--color-ink);
            box-shadow: 0 0 20px var(--color-accent-soft);
        }

        .footer-links { margin-top: 32px; font-size: 13px; color: #94A3B8; }
        .footer-links a { color: var(--color-accent); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="split-container">
    <div class="panel-left">
        <div class="floating-ring" style="width: 250px; height: 250px; left: -5%; top: 10%; animation-duration: 20s;"></div>
        <div class="floating-ring" style="width: 150px; height: 150px; right: 10%; bottom: 20%; animation-duration: 12s; animation-delay: 3s;"></div>
        
        <div class="branding">PathIntel.ai</div>
        <div class="quote">"The beautiful thing about learning is that no one can take it away from you."</div>
        <div style="font-size: 0.8rem; color: rgba(255, 255, 255, 0.5); z-index: 2; text-transform: uppercase; letter-spacing: 0.1em;">Join our global cohort of scholars</div>
    </div>

    <div class="panel-right">
        <div class="form-wrapper">
            <h2 class="form-title">Apply.</h2>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="input-group" style="animation-delay: 100ms;">
                    <label class="label">Username</label>
                    <input type="text" name="username" class="input <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" placeholder="Create your unique ID">
                    <?php if(!empty($username_err)): ?>
                        <span class="error-text"><i data-lucide="alert-circle" style="width: 12px;"></i> <?php echo $username_err; ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-group" style="animation-delay: 150ms;">
                    <label class="label">Password</label>
                    <input type="password" name="password" class="input <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Min. 6 characters">
                    <?php if(!empty($password_err)): ?>
                        <span class="error-text"><i data-lucide="alert-circle" style="width: 12px;"></i> <?php echo $password_err; ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-group" style="animation-delay: 200ms;">
                    <label class="label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="input <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" placeholder="Re-enter your password">
                    <?php if(!empty($confirm_password_err)): ?>
                        <span class="error-text"><i data-lucide="alert-circle" style="width: 12px;"></i> <?php echo $confirm_password_err; ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-submit">Initialize Account</button>

                <div class="footer-links">
                    <span>Already enrolled? <a href="login.php">Sign in here</a></span>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>