<?php

require_once currentPath('shop/php/data/product.php');

/** One cart item (one product with a unique ID).
 * There can't be more items with same product IDs in the cart, if user adds
 * item with ID which is already in the cart, only the quantity grows.
 */
class CartItem {
    public $id, ///< productID of the cart item
           $name, $price, $VATPrice, $quantity, $category;
    /**
     * Very simple, just inicialization.
     *
     * @param <int> $id
     * @param <string> $name
     * @param <float> $price
     * @param <int> $quantity
     * @param <int> $category
     *
     */
    function __construct($id, $name, $price, $quantity, $category) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->category = $category;
        $this->VATPrice = $price * Product::$VAT + $price;
    }
}

/** Class representing a cart filled with class of CartItem.
 * There can't be more items with same product IDs in the cart, if user adds
 * item with ID which is already in the cart, only the quantity grows.
 */

class Cart {
    /// array indexed by product IDs
    public $cartItems;

    public $totalPrice; ///< total price of cart content

    /// total price with VAT
    public $totalVATPrice;

    /// constructor only clears the cart
    function __construct() {
        $this->clearCart();
    }

    /**
     * Adds an item to the shopping cart and recounts the total price.
     *
     * @param <int> $id
     * @param <string> $name
     * @param <float> $price
     * @param <int> $quantity
     * @param <int> $category
     */
    function addItem($id, $name, $price, $quantity, $category) {
        $cartItem = &$this->cartItems[$id];
        // use reference to be able to call constructor and assign it to $cartItem
        if ($cartItem == null){
            $cartItem = new CartItem($id, $name, $price, $quantity, $category);
        }else{
            $cartItem->quantity += $quantity;
        }

        $this->totalPrice += $quantity * $price;
        $this->totalVATPrice = $this->totalPrice * Product::$VAT + $this->totalPrice;
    }

    /**
     * Removes an item from a cart.
     * @param <int> $id of item that will be removed
     */
    function removeItem($id) {
        $this->totalPrice -= $this->cartItems[$id]->quantity * $this->cartItems[$id]->price;
        $this->totalVATPrice = $this->totalPrice * Product::$VAT + $this->totalPrice;

        unset($this->cartItems[$id]);
    }

    /**
     * Changes quantity of item with a specified id.
     * @param <int> $id of item that will have its quantity changed
     * @param <int> $quantity
     */
    function changeItemQuantity($id, $quantity) {
        $quantity = (int)$quantity;
        if ($quantity <= 0) // don't want users to remove items from cart using this
            return;

        $this->totalPrice += ($quantity - $this->cartItems[$id]->quantity) * $this->cartItems[$id]->price;
        $this->totalVATPrice = $this->totalPrice * Product::$VAT + $this->totalPrice;

        $this->cartItems[$id]->quantity = $quantity;
    }

    /**
     * Prints cart items using specified printer.
     * @param <type> $cartItemPrinter that prints particular cart items
     */
    function printItems($cartItemPrinter) {
        foreach($this->cartItems as $cartItem) {
            $cartItemPrinter->printCartItem($cartItem);
        }
    }

    /**
     * Clears the shopping cart.
     */
    function clearCart() {
        $this->cartItems = array();
        $this->totalPrice = 0;
        $this->totalVATPrice = 0;
    }

    /**
     * Counts total price and total price with VAT of a cart.
     */
    function countTotalPrice() {
        $this->totalPrice = 0;
        foreach($this->cartItems as $cartItem) {
            $this->totalPrice += $cartItem->price * $cartItem->quantity;
        }
        $this->totalVATPrice = $this->totalPrice * Product::$VAT + $this->totalPrice;
    }

    /**
     * Returns number of items in the cart.
     * @return <int> number of items in the cart
     */
    function itemCount(){
        return count($this->cartItems);
    }

}

/**
 * Cart item printer that prints one item of the cart.
 */
class CartItemPrinter {

    /**
     * Prints a specified cart item as a table row.
     *
     * @param <type> $cartItem to be printed
     */
    function printCartItem($cartItem) {
    ?>
        <tr>
            <td><a href="item.php?id=<?php echo $cartItem->id; ?>"
                   class="name">
                          <?php echo $cartItem->name; ?></a>
            <td><input class="quantity" type="text"
                       name="<?php echo $cartItem->id;?>"
                       value="<?php echo $cartItem->quantity; ?>">
            <td class="price"><?php echo $cartItem->price * $cartItem->quantity; ?>,-
            <td class="VATPrice"><?php echo $cartItem->VATPrice * $cartItem->quantity; ?>,-
            <td><a class="removeItemButton" name="<?php echo $cartItem->id;?>" href="#">
                    <img src="img/removeItem.png" alt="Remove item from cart" width="12" height="12"
                         title="Remove <?php echo $cartItem->name; ?> from cart">
                </a>


   <?php
    }
}

?>
