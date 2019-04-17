<?php

require __DIR__ . '/../vendor/autoload.php';

go(function () {
    defer(function () {
        var_dump(11);
    });
    Co::getContext()[1] = 2;
    var_dump(Co::getContext());
});