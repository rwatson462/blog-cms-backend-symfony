<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {

    // dodgy hack time, to allow API requests from external domains via javascript,
    // we need to send these headers
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Request-Method: GET, POST, PUT');
    header('Access-Control-Allow-Headers: authorization, content-type');

    // No further processing required from an OPTIONS request
    if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
