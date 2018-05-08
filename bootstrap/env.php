<?php

// imports.
use Dotenv\Dotenv;

// try dotenv loading.
try {
    // create the instance.
    $dotEnv = new Dotenv(__DIR__.'/../');
    // call the loader method.
    $dotEnv->load();
// blank catch.
} catch (Exception $e) {
    // nothing.
}