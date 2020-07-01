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
    'DSPTBM' => 'P1000001',                 //'电商平台编码'
    'NSRSBH' => '913101010000000090',          //'纳税人识别码'
    'NSRMC'  => 'XXX官方旗舰店',             //'纳税人名称',
    'XHFMC'  => 'XXX官方旗舰店',              //'销货方名称'
    'XHF_DZ' => '上海市徐汇区XXX号',  //'销货方地址',
    'XHF_DH' => '17621251***',              //'销货方电话',
    'XHF_YHZH' => '87878787878788787878787',  //'销货方银行账号',
    'KPY' => '开票员',                        //'开票员',
    'SKY' => '可选',                            //'收款员（可选）',
    'HSBZ' => '0',
    'TERMINALCODE' => '0',
    'APPID' => 'ZZS_PT_DZFP',
    'TAXPAYWERID' => '913101010000000090',     //'税号',
    'AUTHORIZATIONCODE' => 'NH873FG4KW',      //'授权码',
    'ENCRYPTCODE' =>'2', //0:不加密 1: 3DES 加密 2:CA
    'INTERFACE_FPKJ' => 'ECXML.FPKJ.BC.E_INV',
    'INTERFACE_FPXZ' => 'ECXML.FPXZ.CX.E_INV',
    'INTERFACE_FPYX' => 'ECXML.EMAILPHONEFPTS.TS.E.INV',
    'REQUESTCODE' => 'sdf11dfd1MsfdFWegesdfIK',                //'请求码',
    'RESPONSECODE' => '121',                    //'响应码',
    'PASSWORD' => '',                           //'密码',
    ///'DATAEXCHANGEID' => '交互码',
    'KJFP' => 'ECXML.FPKJ.BC.E_INV',
    'DOWNLOAD' => 'ECXML.FPXZ.CX.E_INV',
    'EMAIL' => 'ECXML.EMAILPHONEFPTS.TS.E.INV',
    'REGISTERCODE' => '922588450019',               //'注册码',
    'SWJG_DM' => '',               //'注册码',
];
```

## 用法

```

    $arr = [
            'invoice_type' => '01',
            'invoice_title' => '测试发票单',
            'items' => [
                [
                    'name' => '治疗',  //项目名称
                    'quantity' => '2',
                    'price' => '100', //项目单价
                    'spbm' => '0000101000000000000', //商品编码 填商品名称对应的商品税收分类编码，19位不足补0
                    'zxbm' => '',    //自行编码
                    'id' => '',      //有折扣时自行编码取值
                    'sl' => '0.06',      //税率
                    'hsbz' => '0',      //含税标志
                ],
            ],
            'discount' => '',
            'mobile' => '17621256***',
            'sum' => '',   //价税合计金额
            'order_bn' => '2492684718573093',   //订单号
            'FPQQLSH' => $ss,  //请求流水号
            'KPXM' => 'sfd', //商品信息中第一条
            'GHFMC' => '张三', //购货方名称
            'GHF_SJ' => '17621256***',   //购货方手机
            'GHFQYLX' => '01',   //购货方企业类型
            'KPLX' => '1',   //开票类型  1 正票 2 红票
            'CZDM' => '10',  //操作代码
            'HJBHSJE' => '',    //合计不含税金额
            'HJSE' => '', //合计税额
            'trade_no' => $ss,  //请求流水号
            'KPHJJE' => '',   //价税合计金额

        ];
        $config = config('invoice');
        $arr = $this->info();
        $obj = new InvoiceSDK($config);
        $res = $obj->create($arr); //开发票
        $res = $obj->download($arr);//下载发票

        $arr['email'] = 'xxxxxxxx@qq.com'; //邮箱
        $arr['fp_dm'] = '031001900411';  //发票代码
        $arr['fp_hm'] = '90111637'; //发票号码
      $res = $obj->email($arr);//给邮箱发送发票
```
