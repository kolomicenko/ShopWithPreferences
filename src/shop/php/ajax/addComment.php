<?php

/** @file
 * Ajax backend to store comments and send them back in html.
 * Sending back in final html structure is better then json or xml, this way
 * javascript doesn't have to know about the html layout, so that it can be just
 * at one place. For more, when htmlspecialchars is applied to the comment, it
 * is more complicated to parse html entities untouched from the xml file. And,
 * every string sent by json must be urlencoded because of newlines or quotes
 * and again decoded by javascript. That's why it is easiest both for
 * server-side and client-side to use plain html.
 */

require_once '../myFunctions.php';
require_once '../connect.php';
require_once '../data/comment.php';

session_start();

// check input
if (!$_SESSION['userLogged'] || $_POST['text'] == "" || $_POST['productID'] <= 0)
    return;

$time = time();
query("insert into comments(userID, productID, time, text)
        values($_SESSION[userID], %d, $time, '%s')", $_POST['productID'], $_POST['text']);

// send the comment to client
$comment = new Comment($time, $_POST['text'], $_SESSION['userName']);
$comment->printMe();

?>

