<?php
require "vendor/autoload.php";

$compiler = PHPCube\Script::file("test.cube.php");
$compiler->compile();
eval($compiler->evalable());
print "\n";

?>