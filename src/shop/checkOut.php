<?php

/**
 * @file
 * This page displays a message that an order has been successfully submitted.
 * Uses CheckOutManager.
 */


  require_once 'php/managers/checkOutManager.php';
  $manager = new CheckOutManager();

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
                




                <div class="cartInfo">Product(s) have been purchased.</div>
                <br class="separator">
                <a class="button yellow fleft" href="catalog.php">
                    Back to home page</a>

            </div>


            <div id="footer"></div>
        </div>
    </body>
</html>