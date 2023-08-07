<?php

$uri = $_SERVER['REQUEST_URI'];
$root = $_SERVER['DOCUMENT_ROOT'];
$uri = $root . dirname($uri) . "/public/" . basename($uri);
if($uri && $uri != __FILE__ && $uri != __DIR__ && file_exists($uri)) {
    require "../vendor/autoload.php";
    $compiler = PHPCube\Script::file($uri);
    $compiler->compile();
    eval($compiler->evalable());
}else{
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    echo "File not found. $uri";
}

?>