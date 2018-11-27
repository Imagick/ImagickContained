<?php

namespace ImagickContained;





//function saneErrorHandler($errorNumber, $errorMessage, $errorFile, $errorLine)
//{
//    if (error_reporting() === 0) {
//        // Error reporting has been silenced
//        if ($errorNumber !== E_USER_DEPRECATED) {
//        // Check it isn't this value, as this is used by twig, with error suppression. :-/
//            return true;
//        }
//    }
//    if ($errorNumber === E_DEPRECATED) {
//        return false;
//    }
//    if ($errorNumber === E_CORE_ERROR || $errorNumber === E_ERROR) {
//        // For these two types, PHP is shutting down anyway. Return false
//        // to allow shutdown to continue
//        return false;
//    }
//    $message = "Error: [$errorNumber] $errorMessage in file $errorFile on line $errorLine.";
//    throw new \Exception($message);
//}

///**
// * Decode JSON with actual error detection
// *
// * @param mixed $json
// * @return mixed
// * @throws \Example\Exception\JsonException
// */
//function json_decode_safe($json)
//{
//    if ($json === null) {
//        throw new \Example\Exception\JsonException("Error decoding JSON: cannot decode null.");
//    }
//
//    $data = json_decode($json, true);
//
//    if (json_last_error() === JSON_ERROR_NONE) {
//        return $data;
//    }
//
//    $parser = new \Seld\JsonLint\JsonParser();
//    $parsingException = $parser->lint($json);
//
//    if ($parsingException !== null) {
//        throw $parsingException;
//    }
//
//    if ($data === null) {
//        throw new \Example\Exception\JsonException("Error decoding JSON: null returned.");
//    }
//
//    throw new \Example\Exception\JsonException("Error decoding JSON: " . json_last_error_msg());
//}






function convertToValue($name, $value)
{
    if (is_scalar($value) === true) {
        return $value;
    }
    if ($value === null) {
        return null;
    }

    $callable = [$value, 'toArray'];
    if (is_object($value) === true && is_callable($callable)) {
        return $callable();
    }
    if (is_object($value) === true && $value instanceof \DateTime) {
        return $value->format(DATE_ATOM);
    }

    if (is_array($value) === true) {
        $values = [];
        foreach ($value as $key => $entry) {
            $values[$key] = convertToValue($key, $entry);
        }

        return $values;
    }

    $message = "Unsupported type [" . gettype($value) . "] for toArray for property $name.";


    if (is_object($value) === true) {
        $message = "Unsupported type [" . gettype($value) . "] of class [" . get_class($value) . "] for toArray for property $name.";
    }

    throw new \Exception($message);
}



/**
 * Self-contained monitoring system for system signals
 * returns true if a 'graceful exit' like signal is received.
 *
 * We don't listen for SIGKILL as that needs to be an immediate exit,
 * which PHP already provides.
 * @return bool
 */
function checkSignalsForExit()
{
    static $initialised = false;
    static $needToExit = false;

    $fnSignalHandler = function ($signalNumber) use (&$needToExit) {
        $needToExit = true;
    };

    if ($initialised === false) {
        pcntl_signal(SIGINT, $fnSignalHandler, false);
        pcntl_signal(SIGQUIT, $fnSignalHandler, false);
        pcntl_signal(SIGTERM, $fnSignalHandler, false);
        pcntl_signal(SIGHUP, $fnSignalHandler, false);
        pcntl_signal(SIGUSR1, $fnSignalHandler, false);
        $initialised = true;
    }

    pcntl_signal_dispatch();

    return $needToExit;
}


/**
 * Repeatedly calls a callable until it's time to stop
 *
 * @param callable $callable - the thing to run
 * @param int $secondsBetweenRuns - the minimum time between runs
 * @param int $sleepTime - the time to sleep between runs
 * @param int $maxRunTime - the max time to run for, before returning
 */
function continuallyExecuteCallable($callable, int $secondsBetweenRuns, int $sleepTime, int $maxRunTime)
{
    $startTime = microtime(true);
    $lastRuntime = 0;
    $finished = false;

    echo "starting continuallyExecuteCallable \n";
    while ($finished === false) {
        $shouldRunThisLoop = false;
        if ($secondsBetweenRuns === 0) {
            $shouldRunThisLoop = true;
        }
        else if ((microtime(true) - $lastRuntime) > $secondsBetweenRuns) {
            $shouldRunThisLoop = true;
        }

        if ($shouldRunThisLoop === true) {
            $callable();
            $lastRuntime = microtime(true);
        }

        if (checkSignalsForExit()) {
            break;
        }

        if ($sleepTime > 0) {
            sleep($sleepTime);
        }

        if ((microtime(true) - $startTime) > $maxRunTime) {
            echo "Reach maxRunTime - finished = true\n";
            $finished = true;
        }
    }

    echo "Finishing continuallyExecuteCallable\n";
}


function testImagickQueue()
{
    $radius = 5;
    $sigma = 1;
    $channel = \Imagick::CHANNEL_ALL;

    $imagePath = __DIR__ . "/../input/IMG_2561_480.jpg";

    $imagick = new \Imagick(realpath($imagePath));
    $imagick->adaptiveBlurImage($radius, $sigma, $channel);

    $imagick->writeImage(__DIR__ . "/../output/foo.jpg");
}
