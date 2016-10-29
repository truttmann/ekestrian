<?php
$oputput = "";
exec('php '.__DIR__.'/index.php check-enchere -v', $output);

var_dump($output);
?>	