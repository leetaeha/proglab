<?php

$file = $_FILES["file"];

$f_name = $file['name'];
$f_type = $file['type'];
$f_url = $file['temp_name'];
$f_error = $file['error'];
$f_size = $file['size'];


print_r($file);
echo "$file....$f_name..$f_type..$f_url...$f_error....$f_size.....<br>";

?>