<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeRedirectedFor', function (string $url) {
    expect($this->value->status())->toBe(302);
    expect($this->value->headers->get('Location'))->toBe($url);
});
