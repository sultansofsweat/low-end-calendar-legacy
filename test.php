<?php
    require("functions.php");
    /*$oldhash=md5("bunchofjunk");
    var_dump(password_needs_rehash($oldhash,PASSWORD_DEFAULT));
    $newhash=password_hash("bunchofjunk",PASSWORD_DEFAULT);
    var_dump(password_needs_rehash($newhash,PASSWORD_DEFAULT));*/
    
    $timeTarget = 0.08;

    $cost = 8;
    do {
        $cost++;
        $start = microtime(true);
        password_hash("test", PASSWORD_BCRYPT, array("cost" => $cost));
        $end = microtime(true);
    } while (($end - $start) < $timeTarget);
    
    echo "Appropriate Cost Found: " . $cost . " compared to " . get_password_cost();
?>