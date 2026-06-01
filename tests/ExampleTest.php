<?php

class ExampleTest extends TestCase
{
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function test_basic_example(): void
    {
        $this->visit('/')
            ->see('Laravel');
    }
}
