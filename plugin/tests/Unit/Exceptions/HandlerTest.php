<?php

namespace Tests\Unit\Exceptions;

use Illuminate\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use Symfony\Component\HttpFoundation\Response;
use TTU\Charon\Exceptions\Handler;
use Tests\TestCase;
use TTU\Charon\Exceptions\RegistrationException;

class HandlerTest extends TestCase
{
    public function testRenderWithJsonAndStatusIfRegistrationException()
    {
        /** @var Request|Mock $request */
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('expectsJson')->once()->andReturn(true);

        $exception = new RegistrationException('invalid_chosen_time');

        $handler = new Handler(Container::getInstance());

        /** @var JsonResponse|Response $response */
        $response = $handler->render($request, $exception);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Invalid chosen time!', $response->getData()->title);
    }
}
