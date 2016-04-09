<?php

/**
 * @file
 *
 * Displays shopping cart, uses CartManager. Also displays order preferences
 * such as payment options and shipping options. Users can alter shopping cart
 * content by changing quantity or deleting particular items. This is done by ajax,
 * see documentation of a class CartControls in main.js.
 */

  require_once 'php/managers/cartManager.php';
  $manager = new CartManager();


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="css/style.css">

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery.cookie.js"></script>
        <script type="text/javascript" src="js/main.js"></script>

        <script type="text/javascript" src="js/cart.js"></script>

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
                <div class="cartInfo<?php if ($manager->cart->itemCount() > 0) { echo " noDisplay";} ?>">
                    There are no items in your shopping cart.
                </div>
                <div class="separator"></div>
                  <?php if ($manager->cart->itemCount() > 0) { ?>
                <table class="cart">
                    <thead>
                        <tr><td>Product name<td>Quantity<td>Price<td>Price incl. VAT

                    <tbody>
                              <?php
                              $manager->printCartItems();
                              ?>
                        <tr><td colspan="2" class="totalPriceTag">Total price:
                            <td class="price" id="totalPrice">
                                      <?php echo $manager->cart->totalPrice; ?>,-
                            <td class="VATPrice" id="totalVATPrice">
                                      <?php echo $manager->cart->totalVATPrice; ?>,-
                </table>
                      <?php if ($manager->userLogged) { ?>
                <div id="orderPrefs">
                    <p class="orderPrefCaption">Select shipping method:</p>
                    <table class="prefTypeTable">
                                  <?php
                                  foreach ($manager->shippingTypes as $type) {

                                      ?>
                        <tr><td class="center">
                                <input type="radio" name="TopShippingType" value="<?php echo $type->id; ?>"
                                              <?php if ($type->id == $manager->shippingType) { ?>
                                       checked="checked"
                                                     <?php } ?>><br>
                                <b><?php echo $type->price; ?>,-</b>
                            <td><b><?php echo $type->name; ?></b><br>
                                <?php echo $type->description; ?>
                            

                                          <?php } ?>


                    </table>
                    <p class="orderPrefCaption">Select payment method:</p>
                    <table class="prefTypeTable">
                                  <?php
                                  foreach ($manager->paymentTypes as $type) {

                                      ?>
                        <tr><td class="center">
                                <input type="radio" name="TopPaymentType" value="<?php echo $type->id; ?>"
                                              <?php if ($type->id == $manager->paymentType) { ?>
                                       checked="checked"
                                                     <?php } ?>><br>
                                <b><?php echo $type->price; ?>,-</b>
                            <td><b><?php echo $type->name; ?></b><br>
                                <?php echo $type->description; ?>

                                          <?php } ?>


                    </table>
                </div>
                      <?php } ?>


                <br class="separator">
                <img class="fright noDisplay" style="margin-right: 20px;"
                     id="loadingGif" src="img/loading.gif" alt="">

                      <?php if ($manager->userLogged) { ?>
                <a class="button red fright" href="#" id="submitButton">
                    Continue order</a>
                      <?php }else { ?>

                <a class="button black fright" title="You must be logged in"
                   onclick="alert('Please log in.');">
                    Continue order</a>

                      <?php }
                  } ?>

                <a class="button yellow fleft" href="<?php echo htmlspecialchars($manager->lastShoppingPage) ?>">
                    Continue shopping</a>
            </div>


            <div id="footer"></div>
        </div>
    </body>
</html>