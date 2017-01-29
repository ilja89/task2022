<?php

use Illuminate\Http\Request;

function getMoodleRequest($route = null, $method = null) {

    $currentRequest = Request::capture();
    $newRoute = $route !== null
        ? $currentRequest->server('REQUEST_SCHEME') . '://' . $currentRequest->server('HTTP_HOST') . '/mod/charon/' . $route
        : $currentRequest->route();
    $newMethod = $method !== null ? $method : $currentRequest->method();
    /** @var \Illuminate\Http\Request $request */
    $request  = Request::create($newRoute, $newMethod,
        $currentRequest->all(), $currentRequest->cookies->all(), $currentRequest->allFiles(),
        [
            'DOCUMENT_ROOT'   => $currentRequest->server('DOCUMENT_ROOT'),
            'SCRIPT_FILENAME' => $currentRequest->server('DOCUMENT_ROOT') . '/mod/charon/index.php',
            'SCRIPT_NAME'     => '/mod/charon/index.php',
            'PHP_SELF'        => '/mod/charon/index.php',
        ]);

    return $request;
}
