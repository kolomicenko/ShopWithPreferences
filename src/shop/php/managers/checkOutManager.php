<?php

require_once 'php/managers/manager.php';

/**
 * A manager for finished orders. Very simple, just adds order to database and
 * clears the cart.
 */
class CheckOutManager extends Manager {

    function __construct() {

        parent::__construct();

        //checkOut must be called by POST, too
        if (isset($_POST['checkOut'])) {
            if ($_SESSION['cart']->itemCount() == 0) { // fake input
                header("location: $_SERVER[HTTP_REFERER]");
                exit;
            }

            // save the order to database
            $this->saveOrder();

            // call blackbox's function to recount order ratings
            $this->bb->setPreference("LinkedItems", $this->cart);

            // clear the shopping cart
            $this->cart->clearCart();            

            //redirect
            header("location: $_SERVER[PHP_SELF]");

        }




    }

    /**
     * Saves details about the order to the database. First saves info about 
     * the order itself into orders table, then saves all the purchased products 
     * into orderProducts table. Call this function when an order of products
     * is succesfully completed.
     * 
     */
    function saveOrder() {

        $shippingType = $this->bb->getPreference("TopShippingType");
        $paymentType = $this->bb->getPreference("TopPaymentType");

        query("INSERT INTO orders (userID, time, shippingType, paymentType)
               VALUES ($this->userID, %d, $shippingType, $paymentType)",
                    time());

        // get the orderID
        $orderID = mysql_insert_id();

        //prepare the query
        $query = "INSERT INTO orderProducts (orderID, productID, quantity) VALUES ";
        $comma = "";

        foreach($this->cart->cartItems as $cartItem){
            $query .= $comma . "(" . $orderID . "," . $cartItem->id . ","
                        . $cartItem->quantity . ")";
            $comma = ",";
        }

        // execute the query
        query($query);

    }





}