<?php

if (isset($_POST["pass_check"]) && $Securimage->check($_POST['captcha_code']) !== false) {
    $_SESSION["captcha_wall"] = true;
    Redirect("");
} else {
    Redirect("error?error=INVALID_CAPTCHA");
}