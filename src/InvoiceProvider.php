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
    /**
     * 指示是否推迟提供程序的加载
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * 服务引导方法
     *
     * @return void
     */
    public function boot()
    {
        // Config path.
        $config_path = __DIR__.'/config/invoice.php';

        // 发布配置文件到项目的 config 目录中.
        $this->publishes(

            [$config_path => config_path('invoice.php')],
            'invoice'
        );
    }

    /**
     * 注册服务
     */
    public function register()
    {
        $this->app->singleton('invoice', function ($app) {
            return new InvoiceSDK($app['config']);
        });
        // Config path.
        $config_path = __DIR__.'/config/invoice.php';

        // 发布配置文件到项目的 config 目录中.
        $this->mergeConfigFrom(
            $config_path,
            'invoices'
        );
    }
}
