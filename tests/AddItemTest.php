<?php

use controller\addItem;
use PHPUnit\Framework\TestCase;

class AddItemTest extends TestCase
{
    private addItem $addItem;

    protected function setUp(): void
    {
        $this->addItem = new addItem();
    }

}
