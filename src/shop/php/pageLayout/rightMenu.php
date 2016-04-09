<?php

/**@file
 * Right menu of the page with suggested items. In the right menu there are
 * allways some items suggested to the user, depending on at which page user
 * is. This is done by printRightMenu() function, which is virtual. Catalog
 * has 'Top Items', item.php has 'Alternative Items' (= similar items), cart.php
 * has 'Relative Items' (=linked items) to all items in the cart and
 * itemAdded.php has also relative items, but only relative to the item just
 * being added to the cart, not to the items that are already there.
 */
  
?>

      <div id="rightMenu" class="menu">          
          
<?php
  
  $manager->printRightMenu();


?>
      </div>