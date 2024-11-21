<?php

namespace LaraBoy\LaraQrcode\Tests;

use Orchestra\Testbench\TestCase;
use LaraBoy\LaraQrcode\Facades\QrCode;

class ExampleTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\LaraBoy\LaraQrcode\QrCodeServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'QrCode' => \LaraBoy\LaraQrcode\Facades\QrCode::class,
        ];
    }

    public function testQrCodeGeneration()
    {
        $qrcode = QrCode::generate('Hello World');
        $this->assertNotEmpty($qrcode);
    }
}
