<?php

require_once 'php/managers/shoppingManager.php';
require_once 'php/data/checkOutTypes.php';


/**
 * A manager for cart.php. The only job is to prepare shipping and payment types.
 * Linked (or Related) items are printed in the right menu. 
 */

class CartManager extends ShoppingManager {

    public $shippingTypes = array();
    public $paymentTypes = array();

    public $shippingType = 0;
    public $paymentType = 0;    

    function __construct() {

        parent::__construct();        

        if ($this->userLogged && $this->cart->itemCount() > 0){
            // get shipping types and top shipping type
            $sqlTypes = query("SELECT * FROM shippingtypes");
            while ($type = mysql_fetch_assoc($sqlTypes)) {
                $this->shippingTypes[] = new ShippingType($type['ID'],
                                                          $type['name'],
                                                          $type['price'],
                                                          $type['description']);
            }
            $this->shippingType = $this->bb->getPreference("TopShippingType");

            // get payment types and top payment type
            $sqlTypes = query("SELECT * FROM paymenttypes");
            while ($type = mysql_fetch_assoc($sqlTypes)) {
                $this->paymentTypes[] = new PaymentType($type['ID'],
                                                        $type['name'],
                                                        $type['price'],
                                                        $type['description']);
            }
            $this->paymentType = $this->bb->getPreference("TopPaymentType");
        }
    }

    /**
     * Prints cart items using printer CartItemPrinter.
     */
    function printCartItems(){
        $this->cart->printItems(new CartItemPrinter());
    }

    
    /**
     * Prints right menu with linked (related) items.
     */
    function printRightMenu() {
        ?>
<div class="caption" style="background-image: url('img/rightMenuRI.png');"></div>

        <?php

        // comma separated list of product IDs
        $ids = implode(',', array_keys($this->cart->cartItems));
        $result = $this->bb->getPreference("LinkedItems", 5, $ids);

        while($line = mysql_fetch_assoc($result)) {
            ?>
<div class="item">
    <img width="190" height="12" alt="" src="img/top_bg_right.gif">
    <a href="item.php?id=<?php echo $line['productID']; ?>&amp;cat=<?php echo $line['categoryID'] ?>">
        <span class="name"><?php echo $line['productName']?></span>
        <span class="price"><?php echo $line['productPrice']?>,-</span>
        <br>
        <img src="<?php echo $line['imgPath']; ?>" alt="">
    </a>
    <img width="190" height="12" alt="" src="img/bot_bg_right.gif">
</div>

        <?php

        }
    }

}



?>