<?php
if(!$config["online_mode"]) {
    include __DIR__ . "/lib/database.php";
} else {
    $dbh = null;
}
function Autoloader($class_name){
    //Split the class name up if it contains backslashes.
    $class_name_parts = explode('\\', $class_name);
    //The last piece of the array will be our class name.
    $class_name = end($class_name_parts);
    //Include the class.
    include __DIR__ . '/lib/' . $class_name . '.php';
}

//Tell PHP what our autoloading function is.
spl_autoload_register('Autoloader');
