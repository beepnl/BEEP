<?php

final class ExampleTest extends TestCase
{
    /**
     * A basic functional test example.
     */
    public function test_basic_example(): void
    {
        $this->visit('/')
            ->see('Laravel');
    }
}
