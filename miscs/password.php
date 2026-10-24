<?php
$password = 12345;
$hashed_password = md5($password);

echo "password: ", $password, "<br>";
echo "hashed password: ", $hashed_password, "<br>";

?>