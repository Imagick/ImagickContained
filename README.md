# ImagickContained


A safer way of running Imagick/ImageMagick


## Adding to docker compose

Add something like this to your docker-compose file.

```
  imagick_contained:
    build: vendor/danack/imagick_contained
    ports:
      - "6380:6379"
    volumes:
      - ./images/input:/var/app/images/input
      - ./images/output:/var/app/images/output
      - ./lib:/var/app/lib
      - ./vendor:/var/app/vendor
```