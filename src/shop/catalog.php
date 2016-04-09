<?php

/**
 * @file
 *
 * Prints item catalog and its controls, uses CatalogManager. Catalog controls
 * (paging, item sorting and number of items on a page) are displayed on top
 * and at the bottom of the page. If currentCategory is 0, which means user
 * is on home page, displays quick user guide. See startGuide.js and class
 * PageNumbers in main.js.
 */

require_once 'php/managers/catalogManager.php';
$manager = new CatalogManager();

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="css/style.css">

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery.cookie.js"></script>
        <script type="text/javascript" src="js/main.js"></script>
        <?php if ($manager->currentCategory > 0 && $manager->pagesCount > 1) { ?>
        <script type="text/javascript" src="js/catalog.js"></script>
        <?php
        }
        if ($manager->currentCategory == 0){
        ?>
        <script type="text/javascript" src="js/startGuide.js"></script>
        <?php } ?>
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
                <?php

                if ($manager->currentCategory != 0) {
                    if (count($manager->products) == 0) {
                        ?>
                <div class="pageEmpty">This category is now empty.</div>

                    <?php
                    }else {

                        $manager->printControlsForm('Top');

                        ?>

                <div class="separator"></div>
                        <?php

                        $counter = 0;
                        foreach ($manager->products as $product) {
                            if($counter == $manager->productsInRow) {
                                $counter = 0;
                                echo '<div class="separator"></div>';
                            }
                            ++$counter;
                            ?>
                <div class="item">
                    <div class="name">
                        <a href="item.php?id=<?php echo $product->id; ?>&amp;cat=<?php echo $manager->currentCategory; ?>">
                                    <?php echo $product->name; ?></a>
                    </div>

                    <div class="image">
                        <a href="item.php?id=<?php echo $product->id; ?>&amp;cat=<?php echo $manager->currentCategory; ?>">
                            <img width="120" height="90" src="<?php echo $product->imagePath; ?>" alt="">
                        </a>
                    </div>
                    <div class="attributes">
                        <div class="fading"></div>
                        <table>
                                        <?php
                                        foreach ($product->attributes as $attr) {
                                            ?>
                            <tr><td><?php echo $attr->name ?><td>
                                                    <?php printf($attr->formatString,$attr->value);
                                                }
                                                ?>
                        </table>
                    </div>
                    <div class="bottomRow">
                        <span class="price">
                                        <?php echo $product->price ?>,-
                        </span>
                        <span class="VATPrice">
                            (incl. VAT <?php echo $product->VATPrice ?>,-)
                        </span>
                        <a class="button red fright" href="javascript:postData('itemAdded.php',{'addId':'<?php
                                       echo $product->id;?>','quantity':'1'})">
                            Add to cart
                        </a>


                    </div>

                </div>
                        <?php
                        } //foreach
                        ?>
                <div class="bottomSeparator"></div>
                        <?php
                        $manager->printControlsForm('Bottom');
                    } //else (not empty)
                
                } // if (cat)
                else {
                    ?>
                <div id="startGuide" style="display: none;">
                    <img src="img/startGuide<?php if ($manager->userLogged) echo 'Logged';?>.png"
                         width="972" height="321" alt="" usemap="#guideMap">
                    <img src="img/startGuideItem.png"
                         alt="" width="650" height="550">
                    <map name="guideMap">
                        <?php if (!$manager->userLogged){ ?>
                        <area alt="" shape="rect" coords="625,20,710,35"
                              href="profile.php">
                        <?php } ?>
                        <area alt="" shape="rect" coords="385,90,500,120"
                              href="profile.php">
                        <area alt="" shape="rect" coords="255,90,385,120"
                              href="cart.php">
                        <area alt="" shape="rect" coords="780,1,925,40"
                              href="cart.php">
                    </map>
                </div>
                <div id="showGuideAgain">
                    <span>Click here to see Quick Start Guide.</span>
                    <?php if (!$manager->userLogged){ ?>
                    <div class="registerGuide" >
                        <img alt="" src="img/startGuideArrow.png"
                             width="44" height="165">
                        <div style="text-decoration: none;">
                            Continue with logging in<br>
                            or registering.
                        </div>
                    </div>
                    <?php } ?>
                </div>
                    
                
                <?php } ?>
            </div> <!-- main -->
            <div id="footer"></div>
        </div> <!-- entirePage -->
    </body>
</html>