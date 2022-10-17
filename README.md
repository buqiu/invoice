# invoice

## desc

上海航天电子发票对接平台，PHP版本的SDK

## 安装

```
composer require buqiu/invoice
```

#### 发布配置

如果您希望覆盖存储库和条件所在的路径,请发布配置文件:

```shell script
php artisan vendor:publish
```

然后只需打开 `config/invoice.php` 并编辑即可！

#### 如果你想使用 `Facade`

##### 在 `config\app.php` 找到 `providers` 数组，添加刚创建的类

```
Buqiu\Invoice\InvoiceProvider::class
```

##### 在 `config\app.php` 找到 `aliaes` 数组，添加刚创建的类

```
'InvoiceSdkFacade' =>   Buqiu\Invoice\Facades\InvoiceSdkFacade::class
```

#### 配置文件

```
return [
    // 开放平台appKey
    'app_key' => '',
    // 开放平台appSecret
    'app_secret' => '',
    // 临时授权码
    'code' => '',
    // 授权商户税号
    'tax_num' => '',
    // 授权回调地址
    'redirect_uri' => '',
    // 环境 沙箱环境https://sandbox.nuonuocs.cn/open/v1/services 正式环境https://sdk.nuonuo.com/open/v1/services
    'url' => '',
    // 开票回调地址
    'call_back_url' => '',
    // 销方电话
    'saler_tel' => '',
    // 销方地址
    'saler_address' => '',
    // 销方银行开户行及账号(二手车销售统一发票时必填)
    'saler_account' => '',
    // 复核人
    'checker' => '',
    // 收款人
    'payee' => '',
    // 部门门店id（诺诺系统中的id）
    'department_id' => '',
    // 开票员id
    'clerk_id' => '',
    // 开票员（全电发票时需要传入和开票登录账号对应的开票员姓名）
    'clerk' => '',
    // 单价含税标志：0:不含税,1:含税
    'with_tax_flag' => '',
    // 是否机动车销售统一发票
    'is_vehicle' => '0',
    // 开具二手车销售统一发票才需要传
    'is_second_hand_car' => '0',
    // 分机号（只能为空或者数字）
    'extension_number' => '',
    // 终端号（开票终端号，只能 为空或数字）
    'terminal_number' => '',
    // 机器编号（12位盘号）
    'machine_code' => '',
];
```

## 用法

```
    public function api()
    {
        $token = InvoiceSDK::getMerchantToken();// 访问令牌 
        // API方法名 :
        // nuonuo.ElectronInvoice.requestBillingNew 开具发票
        // nuonuo.ElectronInvoice.queryInvoiceResult 获取发票结果
        $method = "";
        $body = InvoiceSDK::getBody($params,$method); // 获取过滤参数
        $senid = "唯一标识，32位随机码";
        $res = InvoiceSDK::sendPostSyncRequest($senid, $token->access_token, $method, $body);

        return $res;
    }
```
