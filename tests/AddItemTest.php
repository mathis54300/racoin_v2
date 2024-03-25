<?php

use PHPUnit\Framework\TestCase;

class AddItemTest extends TestCase
{
    private $addItem;

    protected function setUp(): void
    {
        $this->addItem = new AddItem();
    }

    public function testIsEmailWithValidEmail()
    {
        $this->assertTrue($this->addItem->isEmail('test@example.com'));
    }

    public function testIsEmailWithInvalidEmail()
    {
        $this->assertFalse($this->addItem->isEmail('test@example'));
    }
}
