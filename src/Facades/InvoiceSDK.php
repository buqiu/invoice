<?php

namespace Buqiu\Invoice\Facades;

use Illuminate\Support\Facades\Facade;

class InvoiceSDK extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'InvoiceSDK';
    }
}