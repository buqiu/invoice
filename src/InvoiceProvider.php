<?php
/**
 * Name: 服务提供者.
 * User: 董坤鸿
 * Date: 2020/06/23
 * Time: 14:19
 */

namespace Buqiu\Invoice;

use Illuminate\Support\ServiceProvider;

class InvoiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * 服务引导方法
     *
     * @return void
     */
    public function boot()
    {
        //发布配置文件到项目的 config 目录中
        $this->publishes(
            [
                __DIR__.'/invoice.php' => config_path('invoice.php'),
            ]
        );
    }

    /**
     * 注册服务
     */
    public function register()
    {

    }
}
