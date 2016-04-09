<?php
/**@file
 * Left menu of the page mostly with category tree. To be able to have the
 * left menu different for every page, manager's virtual function printLeftMenu
 * is used.
 */
?>

      <div id="leftMenu" class="menu">          
        <?php
          
          $manager->printLeftMenu();
          
        ?>
      
      </div>