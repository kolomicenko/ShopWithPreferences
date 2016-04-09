<?php

/**
 * @file
 * This page displays a message that an item has been added to the cart.
 * Uses ItemAddedManager.
 */


require_once 'php/managers/itemAddedManager.php';
$manager = new ItemAddedManager();

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



            ?>
            <div id="main">

                <div class="info">Item has been succesfully added to the cart. </div>

                <div class="fright notice">Products you may be interested in
                    <img alt="" width="30" height="10" src="img/redArrow.png">

                </div>

                <br class="separator">

                <a class="button yellow fleft" href="<?php echo htmlspecialchars($manager->lastShoppingPage) ?>">
                    Continue shopping</a>
                <a class="button yellow fright" href="cart.php">
                    Go to Cart</a>



            </div>


            <div id="footer"></div>
        </div>
    </body>
</html>
