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

    public function testAddNewItem()
    {
        $allPostVars = [
            'nom' => 'Test Name',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'ville' => 'Test City',
            'departement' => '1',
            'categorie' => '1',
            'title' => 'Test Title',
            'description' => 'Test Description',
            'price' => '100',
            'psw' => 'password',
            'confirm-psw' => 'password'
        ];

        $result = $this->addItem->addNewItem(null, null, null, $allPostVars);
        $this->assertTrue($result);
    }
}
