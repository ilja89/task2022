<?php

namespace TTU\Charon;

use Illuminate\Http\Request;

if (!function_exists('TTU\\Charon\\get_moodle_request')) {
    function get_moodle_request($route = null, $method = null)
    {

        $currentRequest = Request::capture();
        $newRoute = $route !== null
            ? $currentRequest->server('REQUEST_SCHEME') . '://' . $currentRequest->server('HTTP_HOST') . '/mod/charon/' . $route
            : $currentRequest->route();
        $newMethod = $method !== null ? $method : $currentRequest->method();
        /** @var \Illuminate\Http\Request $request */
        $request = Request::create($newRoute, $newMethod,
            $currentRequest->all(), $currentRequest->cookies->all(), $currentRequest->allFiles(),
            [
                'DOCUMENT_ROOT' => $currentRequest->server('DOCUMENT_ROOT'),
                'SCRIPT_FILENAME' => $currentRequest->server('DOCUMENT_ROOT') . '/mod/charon/index.php',
                'SCRIPT_NAME' => '/mod/charon/index.php',
                'PHP_SELF' => '/mod/charon/index.php',
            ]);

        return $request;
    }
}

if (!function_exists('TTU\\Charon\\get_app')) {
    function get_app()
    {
        require_once __DIR__ . '/../../../../config.php';
        require_once __DIR__ . '/autoload.php';
        return require __DIR__ . '/app.php';
    }
}

if (!function_exists('TTU\\Charon\\handle_moodle_request')) {
    function handle_moodle_request($route, $method)
    {

        $app = get_app();
        $request = get_moodle_request($route, $method);
        $response = $app->make(\Illuminate\Contracts\Http\Kernel::class)->handle($request);

        return $response->getOriginalContent();
    }
}