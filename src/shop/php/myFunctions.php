<?php

/**
 * @file
 * Containts basic functions to be used in every script.
 */

/**
 * Escapes arguments and then calls mysql_query. Accepts variable-length
 * argument list, first argument is text of the query in vsprintf format and all
 * others are escaped and passed as arguments to the vsprintf function. This also
 * makes sure that in place of integer cannot be string and so on.
 * @return <sql result> result of the sql query
 */
function query(){
  $args = func_get_args();
  return mysql_query(vsprintf(array_shift($args),
      array_map('mysql_real_escape_string',$args)));
}

/**
 * Returns sql result with 0 rows.
 *
 * @return <sqlResult> empty sql result
 */
function emptyResult(){
    return query("SELECT 0 FROM DUAL WHERE 0");
}

/**
 * Recursively strips slashes from an array and its values.
 * @param <array> $array to be stripslashed
 * @return <array> stripped array
 */
function stripSlashes_r($array) { 
  return is_array($array) ? array_map('stripSlashes_r', $array) : stripslashes($array); 
}

///Turns off magic_quotes_gpc
function killMagicQuotes(){
    if (get_magic_quotes_gpc()) {
        $_GET    = stripSlashes_r($_GET);
        $_POST   = stripSlashes_r($_POST);
        $_COOKIE = stripSlashes_r($_COOKIE);
    }
}

killMagicQuotes();


/**
 * Returns filename relative to currently executing script. <br>
 * Example: include currentPath('images/a.jpg'); <br>
 * Example return: include '../images/a.jpg'; <br>
 * Just counts number of slashes (-1) in PHP_SELF and prepends that many
 * '../' to the $filename. Very useful when including a script from an already
 * included one (that could be included from different directories).
 *
 * @param <type> $filename Filename relative to the document root.
 */
function currentPath($filename){
    $length = strlen($_SERVER['PHP_SELF']);

    for ($i = 1; $i < $length; $i++){ // or substr_count()
        if ($_SERVER['PHP_SELF'][$i] == '/')
            $filename = '../' . $filename;
    }

    return $filename;
}



?>
