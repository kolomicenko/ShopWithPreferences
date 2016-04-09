<?php
/**@file
 * Connects to the database server.
 */


$link=mysql_connect('localhost','shop','shop');
if (!$link)
    die('Could not connect: ' . mysql_error());
mysql_select_db('shop');


mysql_query("SET CHARACTER SET 'utf8'");



?>