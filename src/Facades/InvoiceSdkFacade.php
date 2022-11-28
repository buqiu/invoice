<?php
/**
 * Name: 发票SDK门面.
 * User: Small_K
 * Date: 2020/06/29
 * Time: 14:54
 */


namespace Buqiu\Invoice\Facades;

use Illuminate\Support\Facades\Facade;

class InvoiceSdkFacade extends Facade
{
    /**
     * Notes: 发票 sdk 门面
     * User : smallK
     * Date : 2022/11/28
     * Time : 10:00
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'invoice';
    }
}