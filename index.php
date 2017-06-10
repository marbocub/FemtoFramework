<?php
require "femtoframework.php";

$app = new FemtoFramework();

$app->get("/", function() use ($app) {
    echo "テストプログラム";
});

$app->get("/hello", function() use ($app) {
    echo "Hello!";
});

$app->get("/hello/", function() use ($app) {
    echo "URLに続けて名前を入れてください";
});

$app->get("/hello/{id}", function() use ($app) {
    $id = $app->arg('id');
    echo "Hello, $id!";
});

$app->get("/hello/{id}/with/{partner}", function() use ($app) {
    $id = $app->arg('id');
    $pa = $app->arg('partner');
    echo "Hello, $id with $pa!";
});

$app->run();
?>