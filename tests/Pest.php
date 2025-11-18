<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(
    Tests\TestCase::class,
    RefreshDatabase::class,
)->in('Feature');

uses(
    Tests\TestCase::class,
    RefreshDatabase::class,
)->in('e2e');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toHaveStatus', function (int $status) {
    return $this->status()->toBe($status);
});

expect()->extend('toHaveMessage', function (string $message) {
    return $this->json('message')->toBe($message);
});
