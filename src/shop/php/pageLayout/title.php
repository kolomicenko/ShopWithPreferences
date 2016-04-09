<?php
/**@file
 * Title of the page, shows link to shopping cart and login form.
 */

?>

<div id="title">
    <a href="catalog.php">
        <img width="171" height="73" class="logo" alt="" src="img/logo.jpg">
    </a>
    <div class="titleObject">
        <a href="cart.php" class="cartLink">
        <img width="24" height="24" alt="" src="img/cart.gif">
        <span class="caption">Shopping cart</span><br>        
        <span id="titleItemCount"><?php echo $manager->cart->itemCount(); ?></span>
        <span class="normal">items</span>
        </a>
    </div>    
    <div class="titleObject">
        <img width="24" height="24" alt="" src="img/user.png">
        <?php

        if ($manager->userLogged) {

            ?>
        <p class="caption"><?php echo $manager->userName; ?></p>
        <p><a href="javascript:postData('catalog.php',{'logout':'yes'})">Log off</a></p>
    
        <?php
            echo "</div>";
        }else {

            ?>
    
        <p><a href="#" id="formToggler">Log in</a></p>
        <p><a href="profile.php">Registration</a></p>
    </div>    
    <form action="catalog.php" method="post" id="loginForm">
        <div>
            <img width="218" height="12" alt="" src="img/top_bg.gif">
            <table>
                <tr><td class="caption">E-mail:<td> <input name="email" type="text" id="loginEmail">
                <tr><td class="caption">Password:<td> <input name="pass" type="password">
            </table>
            <a href="#" id="formSubmit" class="center block">
                <img width="69" height="25" alt="" src="img/enter.gif">
            </a>
            <input type="submit" style="display:none;">
            <input type="hidden" name="login" value="login">
            <img width="218" height="10" alt="" src="img/bot_bg.gif">
        </div>
    </form>
        <?php

        }

        echo $manager->loginMessage;

        ?>

</div><!-- title --> 
