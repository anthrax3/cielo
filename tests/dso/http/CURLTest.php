<?php

use Dso\AbstractTest as AbstractTest;
use Dso\Http\CURL;

class CURLTest extends AbstractTest
{
    public $instance;

    /**
     * Antes de cada teste verifica se a classe existe
     * e cria uma instancia da mesma
     * @return void
     */
    public function assertPreConditions()
    {
        $this->assertTrue(
            class_exists($class = 'Dso\Http\CURL'),
            'Class not found: '.$class
        );
        $this->instance = new CURL();
    }
    public function testInstantiationWithoutArgumentsShouldWork()
    {
        $this->assertInstanceOf('Dso\Http\CURL', $this->instance);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Você deve informar um array.
     */
    public function testSetStrWithInvalidDataShouldWork()
    {
        $this->instance->execute(1);
    }

    // public function testSetStrWithValidDataShouldWork()
    // {
    //     $this->assertEquals($this->instance->execute(array()), );
    // }
}
