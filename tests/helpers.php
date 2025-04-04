<?php
if (!function_exists('dd')) {
    function dd(...$args) {
        foreach ($args as $arg) {
            \Symfony\Component\VarDumper\VarDumper::dump($arg);
        }
        exit(1);
    }
}