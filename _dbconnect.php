<?php
    $server = "localhost";

    $username = "root";
    $password = "";
    $database = "real_estate";

    $conn = mysqli_connect($server, $username, $password, $database);
    if(!$conn)
    {
    //     echo "Connection successful";
    // }
    // else
    // {
        die("Error" .mysqli_connect_error());
    }

?>
