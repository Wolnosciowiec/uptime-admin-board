<?php declare(strict_types = 1);

namespace Riotkit\HealthFlux\Component;

class Config
{
    /**
     * @var array $data
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $key
     * @param array|int|string|null $default
     *
     * @return array|int|string|null|bool
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
}
