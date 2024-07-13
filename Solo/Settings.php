<?php

namespace Solo;

class Settings
{
    private array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get a specific setting by key, or all settings if no key is provided
     * or null if key does not exist
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key = '')
    {
        return (empty($key)) ? $this->settings : $this->settings[$key] ?? null;
    }

}