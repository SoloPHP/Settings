<?php

namespace Solo;

use Exception;

class Settings
{
    private Database $database;
    private string $table;
    private array $settings = [];

    /**
     * Settings constructor.
     *
     * @param Database $database Database connection instance.
     * @param string $table Name of the settings table.
     * @throws Exception If fetching settings fails.
     */
    public function __construct(Database $database, string $table = 'settings')
    {
        $this->database = $database;
        $this->table = $table;
        $this->initSettings();
    }

    /**
     * Load all settings from the database and deserialize if necessary.
     *
     * @throws Exception If the loading fails.
     */
    private function initSettings(): void
    {
        $this->database->query("SELECT name, value FROM ?t", $this->table);

        foreach ($this->database->fetchAll() as $setting) {
            $this->settings[$setting->name] = Utilities::isSerialized($setting->value) ? unserialize($setting->value) : $setting->value;
        }
    }

    /**
     * Get a setting by name.
     *
     * @param string $name The setting name.
     * @return mixed|null The setting value or null if not found.
     */
    public function get(string $name)
    {
        return $this->settings[$name] ?? null;
    }

    /**
     * Get all settings as an associative array.
     *
     * @return array The associative array of all settings.
     */
    public function getAll(): array
    {
        return $this->settings;
    }

    /**
     * Set a setting by name.
     *
     * @param string $name The setting name.
     * @param mixed $value The setting value.
     * @return void
     * @throws Exception
     */
    public function set(string $name, $value): void
    {
        $this->settings[$name] = $value;

        $serializedValue = is_array($value) || is_object($value) ? serialize($value) : $value;
        $this->database->query('INSERT INTO ?t (name, value) VALUES (?s, ?s) ON DUPLICATE KEY UPDATE value = ?s', $this->table, $name, $serializedValue, $serializedValue);
    }

    /**
     * Magic method to get a setting.
     *
     * @param string $name The setting name.
     * @return mixed|null The setting value or null if not found.
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Magic method to set a setting.
     *
     * @param string $name The setting name.
     * @param mixed $value The setting value.
     * @return void
     * @throws Exception
     */
    public function __set(string $name, $value): void
    {
        $this->set($name, $value);
    }
}