# ImagickContained

A safer way of running Imagick/ImageMagick

## Intro

This container uses Supervisor to run some background workers that will process images for you.

This should make Imagick be a lot safer to use as:

* It avoids leaving open web requests for a long time.

* It means that any exploit that exists can't access most of your system.


This project is currently a 'proof-of-concept'. It needs quite a bit of work to be made production ready. Issues are here: https://github.com/Danack/ImagickContained/issues


## Adding to docker compose

Add something like this to your docker-compose file.

```
  imagick_contained:
    build: vendor/danack/imagick_contained
    volumes:
      - ./images/input:/var/app/images/input
      - ./images/output:/var/app/images/output
      - ./lib:/var/app/lib
      - ./vendor:/var/app/vendor
```


Tell your app how to create RedisImagickTaskQueue
```
function createRedisImagickTaskQueue()
{
    $host = 'imagick_contained';
    $port = 6379;

    $redis = new \Redis();
    $redis->connect($host, $port);
    //$redis->auth($password);
    $redis->ping();

    return new \ImagickContained\RedisImagickTaskQueue($redis);
}

```


Rather than calling Imagick directly, push the work onto a queue, then check if
the work is done: 

```
    public function getQueuedImage(ImagickTaskQueue $imagickTaskQueue)
    {
        $number = random_int(1000000, 10000000);
        $filename = __DIR__ . "/../../../images/output/ImagickTest_$number.png";

        if (file_exists($filename) === true) {
            return new ImageResponse($filename, ImageResponse::TYPE_PNG);
        }

        $task = ImagickTask::create(
            'Example\ImagickTest::drawFilledPattern',
            [$filename]
        );

        $imagickTaskQueue->pushImagickTask($task);

        // wait for up to 1 second for the image to process
        for ($i=0; $i<200; $i++) {
            if ($imagickTaskQueue->isTaskStatusSuccess($task) === true) {
                return new ImageResponse($filename, ImageResponse::TYPE_PNG);
            }
            usleep(5000);
        }

        return new NotFoundResponse("Image still being processed");
    }
```
