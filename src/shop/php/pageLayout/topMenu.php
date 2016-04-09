<?php

/**@file
 * Top menu of the page. In the top menu there are buttons such as
 * 'contact', 'home' or 'support'.
 */

?>

      <div id="topMenu" class="menu">        
        <ul>
          <li><a href="catalog.php">Home</a></li>
          <li><img src="img/separator.gif" width="2" height="29" alt=""></li>
          <li><a href="cart.php">Shopping cart</a></li>
          <li><img src="img/separator.gif" width="2" height="29" alt=""></li>
<?php
    if ($manager->userLogged){
        ?>        
        <li><a href="profile.php">Edit profile</a></li>
        <li><img src="img/separator.gif" width="2" height="29" alt=""></li>

        <?php
    }else{

?>        
        <li><a href="profile.php">Registration</a></li>
        <li><img src="img/separator.gif" width="2" height="29" alt=""></li>
        <?php
    }

?>          
          <li><a href="catalog.php">Contacts</a></li>
          <li><img src="img/separator.gif" width="2" height="29" alt=""></li>
          <li><a href="catalog.php">News</a></li>
          <li><img src="img/separator.gif" width="2" height="29" alt=""></li>
          <li><a href="catalog.php">About us</a></li>
          <li><img src="img/separator.gif" width="2" height="29" alt=""></li>
        </ul>
      </div>