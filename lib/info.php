<?php

$phpmm = $_GET['e'];

echo "SMTP ERROR: " . $phpmm->getCode() . ": " . $phpmm->getMessage() . " in " . $phpmm->getFile() . "(" . $phpmm->getLine() . ")\n";

?>