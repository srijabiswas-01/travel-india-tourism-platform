<?php require_once 'app_bootstrap.php'; require_once 'security.php'; $registerMode=($_GET['mode']??'')==='register'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login &amp; Register | Travel India</title>
  <link rel="icon" href="img/favicon.jpg?v=1" type="image/jpeg">
  <link rel="stylesheet" type="text/css" href="css/reglog.css">
</head>
<body>
<?php include('header.php'); ?>
<div class="row">
    <div class="col-md-6 mx-auto p-0">
        <div class="card">
            <div class="login-box">
                <div class="login-snip"> 
                    <input id="tab-1" type="radio" name="tab" class="sign-in" <?php echo $registerMode?'':'checked'; ?>>
                    <label for="tab-1" class="tab">Login</label> 
                    <input id="tab-2" type="radio" name="tab" class="sign-up" <?php echo $registerMode?'checked':''; ?>>
                    <label for="tab-2" class="tab">Sign Up</label>
                    <div class="login-space">                      
                        <div class="login">
                          <form action="logins.php" method="post">
                            <?php echo csrf_input(); ?>
                            <div class="group"> 
                                <label for="login-email" class="label">Email Address</label> 
                                <input name="email" id="login-email" type="email" class="input" placeholder="Enter your email address" required>
                            </div>
                            <div class="group"> 
                                <label for="login-password" class="label">Password</label> 
                                <div class="password-field">
                                    <input name="password" id="login-password" type="password" class="input" placeholder="Enter your password" minlength="8" required>
                                    <button class="password-toggle" type="button" data-password-toggle="login-password" aria-label="Show password" title="Show password" aria-pressed="false">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>                    
                            <div class="group"><input type="submit" class="button" value="Sign In"></div>
                            </form>
                        </div>                        
                        <div class="sign-up-form">
                          <form action="regins.php" method="post">
                            <?php echo csrf_input(); ?>
                            <div class="group">
                                <label for="signup-name" class="label">Username</label> 
                                <input name="name" id="signup-name" type="text" class="input" placeholder="Create your username" required> 
                            </div>
                            <div class="group"> 
                                <label for="signup-email" class="label">Email Address</label> 
                                <input name="email" id="signup-email" type="email" class="input" placeholder="Enter your email address" required> 
                            </div>
                            <div class="group"> 
                                <label for="signup-password" class="label">Password</label> 
                                <div class="password-field">
                                    <input name="password" id="signup-password" type="password" class="input" placeholder="Use at least 8 characters" minlength="8" maxlength="72" required>
                                    <button class="password-toggle" type="button" data-password-toggle="signup-password" aria-label="Show password" title="Show password" aria-pressed="false">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>    
                            <div class="group"> 
                                <label for="signup-confirm-password" class="label">Confirm Password</label>
                                <div class="password-field"><input name="confirm_password" id="signup-confirm-password" type="password" class="input" placeholder="Re-enter your password" minlength="8" maxlength="72" required><button class="password-toggle" type="button" data-password-toggle="signup-confirm-password" aria-label="Show password" title="Show password"><i class="fa-solid fa-eye" aria-hidden="true"></i></button></div>
                            </div>
                            <div class="group"> <input type="submit" id="btnsubmit" class="button" value="Sign Up"> </div>
                            </form>
                            <div class="hr"></div>
                            <div class="foot"><label for="tab-1">Already a member? <strong>Sign in</strong></label></div>
                        </div>                  
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
