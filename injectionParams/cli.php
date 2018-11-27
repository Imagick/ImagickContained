<?php

use AurynConfig\InjectionParams;

if (function_exists('injectionParams') == false) {

    function injectionParams() : InjectionParams
    {
        // These classes will only be created once by the injector.
        $shares = [
        ];

        // Alias interfaces (or classes) to the actual types that should be used
        // where they are required.
        $aliases = [

        ];

        // Delegate the creation of types to callables.
        $delegates = [
            \Redis::class => 'createRedis',
        ];

        // Define some params that can be injected purely by name.
        $params = [];

        $prepares = [
        ];

        $defines = [];

        $injectionParams = new InjectionParams(
            $shares,
            $aliases,
            $delegates,
            $params,
            $prepares,
            $defines
        );

        return $injectionParams;
    }
}


return injectionParams();
