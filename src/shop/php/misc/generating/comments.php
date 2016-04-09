<?php

/** @file
 * Generates comments and saves them to the database.
 * ProductID is 0 for these comments to be comments of every product.
 */

return;

require_once '../../connect.php';
require_once '../../myFunctions.php';

$userID = 1;
$time = time();
$productID = 0;
$comments = array(
    "Best thing I've ever seen!",
    "Don't buy this, it's too expensive.",
    "I had to return it after a couple of days of using. It didn't work any more.",
    "I like this product.",
    "Why is this thing so cheap?",
    "Don't tell me the price is correct. I can't believe!",
    "Last night I got drunk so much I bought 100 pieces of this :D",
    "I have it since last week when you had everything 50% off. This item really rocks!",
    "I would NOT get it even if it was for free. I'm so bored with the design.",
    "Buy this ASAP, they're running out of it!");

foreach ($comments as $comment){
    query("insert into comments (userID, productID, time, text) values(
        $userID, $productID, $time, '%s')", $comment);
}






?>
