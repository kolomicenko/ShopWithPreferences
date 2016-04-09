<?php

require_once 'php/managers/manager.php';

/**
 * Father of CartManager and ItemAddedManager. Quite simple, just takes care of
 * the last shopping page (used by 'Continue shopping' button) and overrides
 * the printLeftMenu function.
 */
 
class ShoppingManager extends Manager{
    /// Address of last 'item' or 'catalog' page. This is used by
    /// 'Continue shopping' button in children managers. Defaults to catalog.
    public $lastShoppingPage = 'catalog.php';    

    function __construct() {

        parent::__construct();

        if (isset($_SESSION['lastShoppingPage'])){
            $this->lastShoppingPage = $_SESSION['lastShoppingPage'];
        }
    }

    /// Prints left Menu. The menu will be blank, but with Shopping Cart caption.
    function printLeftMenu(){
        ?>
        <div class="caption" style="background-image: url('img/leftMenuSC.png');"></div>
        <?php

    }
}
?>
