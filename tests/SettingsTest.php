<?php

declare(strict_types=1);

namespace Solo\Settings\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Solo\Settings\Settings;

final class SettingsTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec(
            'CREATE TABLE settings (name TEXT PRIMARY KEY, value TEXT NOT NULL)'
        );
    }

    public function testSetAndGetScalar(): void
    {
        $settings = new Settings($this->pdo);
        $settings->set('site_name', 'Solo');
        self::assertSame('Solo', $settings->get('site_name'));
        self::assertSame('Solo', $settings->site_name);
    }

    public function testSetAndGetArray(): void
    {
        $settings = new Settings($this->pdo);
        $settings->set('options', ['a' => 1, 'b' => 2]);
        $this->assertSame(['a' => 1, 'b' => 2], $settings->get('options'));
    }

    public function testGetAll(): void
    {
        $settings = new Settings($this->pdo);
        $settings->set('x', '1');
        $settings->set('y', '2');
        $all = $settings->getAll();
        $this->assertArrayHasKey('x', $all);
        $this->assertArrayHasKey('y', $all);
    }

    public function testArrayPersistsViaSerialization(): void
    {
        $settings = new Settings($this->pdo);
        $settings->set('arr', ['k' => 'v']);

        // Re-instantiate to force load from DB and ensure unserialize works internally
        $settingsReloaded = new Settings($this->pdo);
        $this->assertSame(['k' => 'v'], $settingsReloaded->get('arr'));
    }
}
