<?php

declare(strict_types=1);

namespace ImagickContained;

class ImagickTask
{
    /**
     * @var
     */
    private $callable;

    /**
     * @var array
     */
    private $params;

    /**
     * @var bool|string
     */
    private $id;

    /**
     * StandardImagickTask constructor.
     * @param $callable
     * @param $params
     */
    private function __construct($callable, array $params, string $id)
    {
        $this->callable = $callable;
        $this->params = $params;
        $this->id = $id;

        // TODO - check params are scalars.
    }

    public static function create($callable, array $params)
    {
        return new self(
            $callable,
            $params,
            bin2hex(random_bytes(32))
        );
    }

    public function getCallable()
    {
        return $this->callable;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function serialize() : string
    {
        $data = [
            'callable' => $this->callable,
            'params'   => $this->params,
            'id'       => $this->id
        ];

        return \json_encode($data);
    }

    public static function deserialize(string $string) : ImagickTask
    {
        $data = json_decode($string, true);

        return new self(
            $data['callable'],
            $data['params'],
            $data['id']
        );
    }
}
