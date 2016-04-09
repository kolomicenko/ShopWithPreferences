<?php

//error_reporting(-1); // to show even notices

require_once 'php/myFunctions.php';
require_once 'php/connect.php';

require_once 'php/blackBox.php';
require_once 'php/cartClass.php';
require_once 'php/data/product.php';

/**
 * Most important manager, it is constructed on every page load. Handles user
 * log in and out, starts session and creates the BlackBox and the Shopping cart.
 * All other managers must inherit from Manager because this functionality is
 * essential. Managers also have virtual functions printLeftMenu and printRightMenu
 * that are called in leftMenu.php resp. rightMenu.php and can be different for
 * every page.
 * <p>
 * There is one more thing Manager has to do, take care of behavior cookies that
 * come to the server side. It only happens with certain browsers and their
 * weird onUnload event handling. This is described on the
 * <a href="index.html">main page</a>.
 */


class Manager{

    public $userName, $userID, $userEmail, $userAddress,
        $userLogged; ///< whether user is logged-in or not

    public $bb; ///< blackBox pointer

    public $loginMessage = ""; ///< Displays whether login/logout was successful

    public $cart;   ///< Shopping cart object. It is a copy of SESSION[cart]

    function __construct() {

        session_start(); // must be called after all class names are read

        if (isset($_POST['login'])) {    
            $users = query("select * from users where userEmail = '%s' and userPassword = '%s'",
                $_POST['email'], sha1($_POST['pass']));
            if (mysql_num_rows($users) == 1) {
                $user = mysql_fetch_assoc($users);
                $_SESSION['userLogged'] = 1;
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['userName'] = $user['userName'];
                $_SESSION['userEmail'] = $user['userEmail'];
                $_SESSION['userAddress'] = $user['userAddress'];

                $_SESSION['loginMessage'] = '<div id="loginMessage" class="green">
                    Login successful.</div>';
            }else {
                $_SESSION['loginMessage'] = '<div id="loginMessage" class="red">
                    Incorrect e-mail or password.</div>';
            }

            // if user was at profile (registration) don't redirect there again
            if (strpos($_SERVER['HTTP_REFERER'], 'profile') === false){
                header("location: $_SERVER[HTTP_REFERER]");
            }else{
                header("location: ".dirname($_SERVER[HTTP_REFERER])."/catalog.php");
            }
            exit;
        }

        if (isset($_POST['logout'])) {
            session_unset();            
            header("location: ".dirname($_SERVER[HTTP_REFERER])."/catalog.php");

            $_SESSION['loginMessage'] = '<div id="loginMessage" class="green">
                    Logout successful.</div>';            
            exit;
        }

        if (isset($_SESSION['userLogged']) && $_SESSION['userLogged'] == 1){
            $this->userName = $_SESSION['userName'];
            $this->userID = $_SESSION['userID'];
            $this->userLogged = $_SESSION['userLogged'];
            $this->userEmail = $_SESSION['userEmail'];
            $this->userAddress = $_SESSION['userAddress'];
        }else{
            $this->userLogged = $_SESSION['userLogged'] = 0;
            $this->userID = $_SESSION['userID'] = 0;
        }

        // create shopping cart or get it from session
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = new Cart();
        }

        $this->cart = $_SESSION['cart'];

        // create the black box
        $this->bb = new BlackBox($_SESSION['userID']);

        // copy login message and unset it (this way it shows just once)
        if (isset($_SESSION['loginMessage'])){
            $this->loginMessage = $_SESSION['loginMessage'];
            unset($_SESSION['loginMessage']);
        }

        // save behavior from cookies only with chrome or safari
        // explained in detail on the main page.        

        if (strpos($_SERVER['HTTP_USER_AGENT'], 'WebKit') !== false){
            $this->dealWithBehaviorCookies();
        }

    }

    /**
     * Handle cookies with behavior on the server side.
     * This is called only when user's browser is Chrome or Safari (WebKit).
     * It call's appropriate blackBox's function and deletes the behavior cookies.
     * This is used only when sending info on unload event was used. If not,
     * client side will take care of the cookies. This is because setting the
     * preference may take significant time so that page loading would be delayed.
     *
     */
    function dealWithBehaviorCookies(){
        if (!isset($_COOKIE['productID']) || $_COOKIE['productID'] <= 0 ||
            !isset($_COOKIE['onUnload'])){ // only when onUnload was fired
            return;
        }

        // set preference
        $this->bb->setPreference("TopItems", $_COOKIE['productID'], $_COOKIE['scrollCount'],
                                     $_COOKIE['clickCount'], $_COOKIE['displayTime'],
                                     $_COOKIE['displayCount']);

        // delete the cookies
        setcookie('productID', '', 1);
        setcookie('scrollCount', '', 1);
        setcookie('clickCount', '', 1);
        setcookie('displayTime', '', 1);
        setcookie('displayCount', '', 1);
        setcookie('onUnload', '', 1);
    }

    /**
     * Prints default left menu. Showing no content, caption image without text.
     */
    function printLeftMenu(){
        ?>
        <div class="caption" style="background-image: url('img/leftMenu.png');"></div>
        <?php

    }
    /**
     * Prints default right menu. Showing no content, caption image without text.
     */
    function printRightMenu(){
        ?>
        <div class="caption" style="background-image: url('img/rightMenu.png');"></div>
        <?php
    }
  
}

?>