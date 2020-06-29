<?php
/**
 * Name: 发票SDK门面.
 * User: Small_K
 * Date: 2020/06/29
 * Time: 14:54
 */


namespace Buqiu\Invoice\Facades;

use Illuminate\Support\Facades\Facade;

class InvoiceSDK extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'InvoiceSDK';
    }
}