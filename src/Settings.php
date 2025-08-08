<?php

declare(strict_types=1);

namespace Solo\Settings;

use PDO;
use PDOException;

class Settings
{
    private PDO $pdo;
    private string $table;
    private array $settings = [];

    /**
     * @param PDO $pdo PDO connection instance
     * @param string $table Settings table name
     */
    public function __construct(PDO $pdo, string $table = 'settings')
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->initializeSettings();
    }

    /**
     * Load all settings from the database and deserialize if necessary.
     */
    private function initializeSettings(): void
    {
        $sql = sprintf('SELECT name, value FROM %s', $this->quoteIdentifier($this->table));
        $statement = $this->pdo->query($sql);
        if ($statement === false) {
            return;
        }

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $name = (string) $row['name'];
            $valueRaw = $row['value'];
            $this->settings[$name] = self::isSerialized($valueRaw) ? unserialize($valueRaw) : $valueRaw;
        }
    }

    /**
     * Get a setting by name.
     *
     * @return mixed|null
     */
    public function get(string $name): mixed
    {
        return $this->settings[$name] ?? null;
    }

    /**
     * Get all settings as an associative array.
     *
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        return $this->settings;
    }

    /**
     * Set a setting by name and upsert into the database.
     *
     * @param string $name
     * @param mixed $value
     */
    public function set(string $name, mixed $value): void
    {
        $this->settings[$name] = $value;
        $serializedValue = (is_array($value) || is_object($value)) ? serialize($value) : (string) $value;

        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $table = $this->quoteIdentifier($this->table);

        if ($driver === 'mysql') {
            $sql = sprintf(
                'INSERT INTO %s (name, value) VALUES (:name, :value) ON DUPLICATE KEY UPDATE value = VALUES(value)',
                $table
            );
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':name' => $name, ':value' => $serializedValue]);
            return;
        }

        if ($driver === 'pgsql') {
            $sql = sprintf(
                'INSERT INTO %s (name, value) VALUES (:name, :value) '
                . 'ON CONFLICT (name) DO UPDATE SET value = EXCLUDED.value',
                $table
            );
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':name' => $name, ':value' => $serializedValue]);
            return;
        }

        if ($driver === 'sqlite') {
            $sql = sprintf(
                'INSERT INTO %s (name, value) VALUES (:name, :value) '
                . 'ON CONFLICT(name) DO UPDATE SET value = excluded.value',
                $table
            );
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':name' => $name, ':value' => $serializedValue]);
            return;
        }

        throw new PDOException('Unsupported PDO driver: ' . (string) $driver);
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Check if a given value is serialized.
     *
     * @param mixed $data
     * @param bool $strict
     */
    private static function isSerialized(mixed $data, bool $strict = true): bool
    {
        if (!is_string($data)) {
            return false;
        }

        $data = trim($data);
        if ($data === 'N;') {
            return true;
        }
        if (strlen($data) < 4 || $data[1] !== ':') {
            return false;
        }

        if ($strict) {
            $lastChar = substr($data, -1);
            if ($lastChar !== ';' && $lastChar !== '}') {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            if ($semicolon === false && $brace === false) {
                return false;
            }
            if ($semicolon !== false && $semicolon < 3) {
                return false;
            }
            if ($brace !== false && $brace < 4) {
                return false;
            }
        }

        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    return substr($data, -2, 1) === '"';
                }
                return strpos($data, '"') !== false;
            case 'a':
            case 'O':
            case 'E':
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E+-]+;{$end}/", $data);
            default:
                return false;
        }
    }

    /**
     * Quote identifier (table) conservatively.
     */
    private function quoteIdentifier(string $identifier): string
    {
        // Allow only simple identifiers (letters, digits, underscore). Prevent SQL injection via table name.
        if (!preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
            throw new PDOException('Invalid identifier provided');
        }

        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        return match ($driver) {
            'mysql' => '`' . $identifier . '`',
            default => '"' . $identifier . '"',
        };
    }
}
