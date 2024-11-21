<?php

namespace LaraBoy\LaraQrcode\Facades;

use Illuminate\Support\Facades\Facade;

class QrCode extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'qrcode';
    }
}
