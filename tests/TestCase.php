<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Mockery is not required to run this suite.
    public $mockConsoleOutput = false;
}
