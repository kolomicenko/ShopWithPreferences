<?php

require_once 'php/managers/shoppingManager.php';

/**
 * The job of this class is adding items to the shopping cart. It adds an item to
 * the cart when POST is set. Otherwise, when GET[id] is set, display items linked
 * to the item with that id. That means when user adds an item to the cart, this
 * manager is first created with POST[id] and then it redirects to itself with
 * GET[id], so that it can show the linked (or related) items.
 *
 */

class ItemAddedManager extends ShoppingManager {

    function __construct() {

        parent::__construct();

        // item is being added
        if (isset($_POST['addId']) && $_POST['addId'] > 0) {
            
            $quantity = (int)$_POST['quantity'];
            if ($quantity <= 0){
                header("location:$_SERVER[HTTP_REFERER]");
                exit;
            }

            $result = mysql_fetch_assoc(query(
                "select * from products where productID = %d", $_POST['addId']));

            $_SESSION['cart']->addItem($result['productID'], $result['productName'],
                $result['productPrice'], $quantity, $result['categoryID']);
            header("location: http://$_SERVER[SERVER_NAME]/shop/itemAdded.php?id=$_POST[addId]");            
            // when redirecting, carry productID in GET to display related items
            exit;
        }
        if (!isset($_GET['id']) || $_GET['id'] <= 0){ // fake input
            header("location: $this->lastShoppingPage");
            exit;
        }
    }

    /**
     * Prints right menu with linked (related) items.
     */
    function printRightMenu() {
        ?>
<div class="caption" style="background-image: url('img/rightMenuRI.png');"></div>

        <?php

        $result = $this->bb->getPreference("LinkedItems", 3, $_GET['id']);

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
