<?php

/** @file
 * Ajax backend to change quantity or remove item from a cart.
 * Decides to remove if sent quantity is 0, otherwise only changes the cart.
 * Returns new price and quantity of the item and new total price and item count.
 */

require_once '../myFunctions.php';
require_once '../connect.php';
require_once '../cartClass.php';
require_once '../data/product.php';

session_start();

// check input
if (!isset($_SESSION['cart']) || !isset($_SESSION['cart']->cartItems[$_POST['id']]))
    exit;

if ($_POST['quantity'] == '0'){ // '0' to check user's typo
    $_SESSION['cart']->removeItem($_POST['id']);
    
    $quantity = 0;
    $price = 0;
    $VATPrice = 0;
}else{    
    $_SESSION['cart']->changeItemQuantity($_POST['id'], $_POST['quantity']);

    $cartItem = $_SESSION['cart']->cartItems[$_POST['id']];
    $quantity = $cartItem->quantity;
    $price = $cartItem->price * $quantity;
    $VATPrice = $cartItem->VATPrice * $quantity;
}

?>

{
    "quantity": <?php echo $quantity; ?>,
    "price": <?php echo $price; ?>,
    "VATPrice": <?php echo $VATPrice; ?>,
    "totalPrice": <?php echo $_SESSION['cart']->totalPrice; ?>,
    "totalVATPrice": <?php echo $_SESSION['cart']->totalVATPrice; ?>,
    "itemCount": <?php echo $_SESSION['cart']->itemCount(); ?>
}
