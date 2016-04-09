<?php

require_once 'php/managers/manager.php';

/**
 * A manager for profile.php, handles registration and profile changes.
 * Messages to the user and values they've filled are carried in session and
 * then put to $profileMessage, $lastMail, $lastName, $lastAddress. To make sure
 * these strings are displayed only once, they are unset on every page load,
 * just after they are shown to the user. This is for user's comfort, not to
 * have to fill particular form fields again. Entered password is not pre-filled,
 * naturally.
 * 
 */
class ProfileManager extends Manager{

    public $profileMessage = "",  ///< Carries messages to the user.
           $lastMail = "", ///< Carries the mail entered at last try.
           $lastName = "", ///< Carries the name entered at last try.
           $lastAddress = ""; ///< Carries the address entered at last try.
           
           

    function __construct() {

        parent::__construct();

        // registration
        if (isset($_POST['reg'])) {
            // some of the required fields are empty
            if (!$_POST['pass'] || !$_POST['pass2'] || !$_POST['name'] || !$_POST['mail']) {
                $_SESSION['profileMessage'] = '<div class="profileInfo red">
                    Please fill in all required fields (marked by an asterisk).</div>';
            }
            // passwords not matching
            else if ($_POST['pass'] != $_POST['pass2']) {
                    $_SESSION['profileMessage'] = '<div class="profileInfo red">
                        Passwords do not match.</div>';
                }
                // email address already used.
                else if (mysql_result(query("select count(*) from users where userEmail = '%s'",
                        $_POST['mail']), 0) > 0) {
                        $_SESSION['profileMessage'] = "<div class=\"profileInfo red\">
                            E-mail \"{$_POST['mail']}\" already in use.</div>";
                    }
                    else {
                        query("INSERT INTO users(userName,userPassword,userEmail,userAddress)
              VALUES('%s','%s','%s','%s')",
                            $_POST['name'],sha1($_POST['pass']),$_POST['mail'],$_POST['address']);
                        $_SESSION['profileMessage'] =
'<div class="profileInfo green">Your account has been successfully created. Now you can log in here.
    <img class="arrow" src="img/profileArrow.png" alt="" width="61" height="124">
</div>';
                    }

            $_SESSION['lastMail'] = $_POST['mail'];
            $_SESSION['lastName'] = $_POST['name'];
            $_SESSION['lastAddress'] = $_POST['address'];

            header("location: $_SERVER[HTTP_REFERER]");           
            exit;



        }
        
        // password change
        if (isset($_POST['changeP'])) {
            if ($_POST['pass'] != '' && $_POST['pass'] == $_POST['pass2']) {
                query("update users set password = '%s' where userID = $this->userID", sha1($_POST['pass']));
                $_SESSION['profileMessage'] = '<div class="profileInfo green">
                    Password successfully changed.</div>';
            }else {
                $_SESSION['profileMessage'] = '<div class="profileInfo red">
                    Passwords do not match.</div>';
            }
            header("location: $_SERVER[HTTP_REFERER]");
            exit;
        }

        // name change
        if (isset($_POST['changeN'])) {
            if ($_POST['name'] != '') {
                query("update users set userName = '%s' where userID = $this->userID", $_POST['name']);
                $_SESSION['profileMessage'] = '<div class="profileInfo green">
                    Name successfully changed.</div>';
                $_SESSION['userName'] = $_POST['name'];
            }else {
                $_SESSION['profileMessage'] = '<div class="profileInfo red">
                    The field "Full name" is not filled out.</div>';
            }
            header("location: $_SERVER[HTTP_REFERER]");
            exit;
        }

        // mail change
        if (isset($_POST['changeM'])) {
            if ($_POST['mail'] != '') {
                if (mysql_result(query("select count(*) from users
                    where userEmail = '%s' and userID <> $this->userID", $_POST['mail']), 0) == 0) {
                    query("update users set userEmail = '%s' where userID = $this->userID", $_POST['mail']);
                    $_SESSION['profileMessage'] = '<div class="profileInfo green">
                    E-mail successfully changed.</div>';
                    $_SESSION['userEmail'] = $_POST['mail'];
                }else {
                    $_SESSION['profileMessage'] = "<div class=\"profileInfo red\">
                            E-mail \"{$_POST['mail']}\" already in use.</div>";
                }
            }else {
                $_SESSION['profileMessage'] = '<div class="profileInfo red">
                    The field "E-mail" is not filled out.</div>';
            }
            header("location: $_SERVER[HTTP_REFERER]");
            exit;
        }

        // address change
        if (isset($_POST['changeA'])) {
            query("update users set userAddress = '%s' where userID = $this->userID", $_POST['address']);
            $_SESSION['profileMessage'] = '<div class="profileInfo green">
                Address successfully changed.</div>';
            $_SESSION['userAddress'] = $_POST['address'];

            header("location: $_SERVER[HTTP_REFERER]");
            exit;
        }



        // message to the user and values filled in the form fields
        if (isset($_SESSION['profileMessage'])){
            $this->profileMessage = $_SESSION['profileMessage'];
            unset($_SESSION['profileMessage']);
        }

        if (isset($_SESSION['lastMail'])){
            $this->lastMail = $_SESSION['lastMail'];
            unset($_SESSION['lastMail']);
        }
        if (isset($_SESSION['lastName'])){
            $this->lastName = $_SESSION['lastName'];
            unset($_SESSION['lastName']);
        }
        if (isset($_SESSION['lastAddress'])){
            $this->lastAddress = $_SESSION['lastAddress'];
            unset($_SESSION['lastAddress']);
        }



    }

    /// Empty left menu with 'User profile' caption.
    function printLeftMenu(){
        ?>
        <div class="caption" style="background-image: url('img/leftMenuU.png');"></div>
        <?php

    }
    /// Right menu will be empty, so will caption.
    function printRightMenu(){
        ?>
        <div class="caption" style="background-image: url('img/rightMenu.png');"></div>
        <?php
    }

}

?>