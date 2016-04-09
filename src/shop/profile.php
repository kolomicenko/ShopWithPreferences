<?php

/**
 * @file
 * This page registers new users or changes details of existing ones.
 * Uses ProfileManager.
 */

require_once 'php/managers/profileManager.php';
$manager = new ProfileManager();


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="css/style.css">

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery.cookie.js"></script>
        <script type="text/javascript" src="js/main.js"></script>
        
        <title>Shop</title>

    </head>
    <body>

        <div id="entirePage">
            <?php
            require_once 'php/pageLayout/title.php';
            require_once 'php/pageLayout/leftMenu.php';
            require_once 'php/pageLayout/topMenu.php';
            require_once 'php/pageLayout/rightMenu.php';

            ?>  <div id="main">

                <?php

                echo $manager->profileMessage;

                if ($manager->userLogged) {

                    ?>

                <form action="profile.php" method="post">
                    <table class="userProfile">
                        <tr><td>Password:<td><input name="pass" type="password" maxlength="50">
                        <tr><td>Retype password:<td><input name="pass2" type="password" maxlength="50">
                            <td><input type="submit" name="changeP" value="Change password" style="width: 100%;">                        
                        <tr><td><div style="height:15px;"></div>
                        <tr><td>E-mail:<td><input name="mail" type="text" value="<?php echo $manager->userEmail; ?>" maxlength="50">
                            <td><input type="submit" name="changeM" value="Change e-mail" style="width: 100%;">
                        <tr><td><div style="height:15px;"></div>
                        <tr><td>Full name:<td><input name="name" type="text" value="<?php echo $manager->userName; ?>" maxlength="50">
                            <td><input type="submit" name="changeN" value="Change name" style="width: 100%;">
                        <tr><td><div style="height:15px;"></div>
                        <tr><td>Full address:<td><textarea name="address" cols="20" rows="3"><?php echo $manager->userAddress; ?></textarea>
                            <td><input type="submit" name="changeA" value="Change address" style="width: 100%;">

                    </table>

                </form>




                <?php

                }else {

                    ?>

                <form action="profile.php" method="post">
                    <table class="userProfile">
                        <tr><td>E-mail:<td><input name="mail" type="text" value="<?php echo $manager->lastMail; ?>" maxlength="50"><td>*
                        <tr><td><div style="height:5px;"></div>
                        <tr><td>Password:<td><input name="pass" type="password" maxlength="50"><td>*
                        <tr><td>Retype password:<td><input name="pass2" type="password" maxlength="50"><td>*
                        <tr><td><div style="height:5px;"></div>
                        <tr><td>Full name:<td><input name="name" type="text" value="<?php echo $manager->lastName; ?>" maxlength="50"><td>*
                        <tr><td><div style="height:5px;"></div>
                        <tr><td>Full address:<td><textarea name="address" cols="20" rows="3"><?php echo $manager->lastAddress; ?></textarea>
                        <tr><td><div style="height:15px;"></div>
                        <tr><td colspan="2" class="center"><input type="submit" name="reg" value="Create account">

                    </table>

                </form>

                <?php

                }

                ?>

            </div> <!-- main -->
            <div id="footer"></div>
        </div> <!-- entirePage -->
    </body>
</html>
