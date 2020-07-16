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

#### 配置文件

```
return [
    // 电商平台编码
    'DSPTBM' => 'P1000001',
    // 纳税人识别码
    'NSRSBH' => '913101010000000090',
    // 纳税人名称
    'NSRMC' => 'XXX官方旗舰店',
    // 销货方名称
    'XHFMC' => 'XXX官方旗舰店',
    // 销货方地址
    'XHF_DZ' => '上海市徐汇区XXX号',
    // 销货方电话
    'XHF_DH' => '17621251***',
    // 销货方银行账号
    'XHF_YHZH' => 'XX支行    87878787878788787878787',
    // 开票员
    'KPY' => '开票员',
    // 收款员（可选）
    'SKY' => '收款员',
    //'复核员'
    'FHR' => '复核员',
    // 含税标志
    'HSBZ' => '0',
    // 终端类型标识
    'TERMINALCODE' => '0',
    // APPID
    'APPID' => 'ZZS_PT_DZFP',
    // 税号
    'TAXPAYWERID' => '913101010000000090',
    // 认证码
    'AUTHORIZATIONCODE' => 'NH873FG4KW',
    // 加密码
    'ENCRYPTCODE' => '2',
    // 发票开具 ECXML.FPKJ.BC.E_INV
    'INTERFACE_FPKJ' => 'ECXML.FPKJ.BC.E_INV',
    // 发票信息下载 ECXML.FPXZ.CX.E_INV
    'INTERFACE_FPXZ' => 'ECXML.FPXZ.CX.E_INV',
    // 邮箱发票推送 ECXML.EMAILPHONEFPTS.TS.E.INV
    'INTERFACE_FPYX' => 'ECXML.EMAILPHONEFPTS.TS.E.INV',
    // 请求码
    'REQUESTCODE' => 'sdf11dfd1MsfdFWegesdfIK',
    // 响应码
    'RESPONSECODE' => '121',
    // 密码
    'PASSWORD' => '',
    // 交互码
    'DATAEXCHANGEID' => '交互码',
    // 发票明细信息下载 ECXML.FPKJ.BC.E_INV
    'KJFP' => 'ECXML.FPKJ.BC.E_INV',
    // 发票信息推送 ECXML.FPXZ.CX.E_INV
    'DOWNLOAD' => 'ECXML.FPXZ.CX.E_INV',
    // 获取企业可用发票数量API ECXML.EMAILPHONEFPTS.TS.E.INV
    'EMAIL' => 'ECXML.EMAILPHONEFPTS.TS.E.INV',
    // 注册码
    'REGISTERCODE' => '922588450019',
    // 电子发票网址
    'HOST' => 'http://xxxxx',
    // 3DES密码
    'KEY' => '*************',
];
```

## 用法

```
    public static function info($order_ids, &$post_data, $no)
    {
        $amount = self::getInvoiceAmountByOrderId($order_ids);
        $invoice_good_code = InvoiceGoodCode::query()->where('id', $post_data['content'])->first();
    
        if($invoice_good_code['preferential_policy'] == 1){
           $yhzcbs =  $invoice_good_code['preferential_policy'];
           $zzstsgl =  $invoice_good_code['preferential_policy_type'];
           $lslbs =  $invoice_good_code['zero_tax_rate_sign'];
        }else{
            $yhzcbs =  $invoice_good_code['preferential_policy'];
            $zzstsgl =  '';
            $lslbs =  '';
        }
    
        $arr = [
            'invoice_type' => '01',
            'invoice_title' => '测试发票单',
            'items' => [
                [
                    'name' => $invoice_good_code['name'],  //项目名称
                    'quantity' => '1',
                    'price' => $amount, //项目单价
                    'spbm' => $invoice_good_code['tax_category_code'], //商品编码 填商品名称对应的商品税收分类编码，19位不足补0
                    'zxbm' => '',    //自行编码
                    'id' => '',      //有折扣时自行编码取值
                    'sl' => $invoice_good_code['tax_rate'],      //税率
                    'hsbz' => $invoice_good_code['tax_mark'],      //含税标志
                    'yhzcbs' => $yhzcbs,      //是否享受优惠政策
                    'zzstsgl' => $zzstsgl,      //优惠政策类型
                    'lslbs' => $lslbs,      //零税率标识
                ],
            ],
            'discount' => '',
            'mobile' => '021-64173538',
            'sum' => '',   //价税合计金额
            'order_bn' => $no,   //订单号
            'FPQQLSH' => $no,  //请求流水号
            'KPXM' => 'sfd', //商品信息中第一条
            'GHFMC' => $post_data['company'], //购货方名称
            'GHF_SJ' => $post_data['mobile'],   //购货方手机
            'GHF_NSRSBH' => $post_data['nor_code'],   //购货方手机
            'GHFQYLX' => '01',   //购货方企业类型
            'KPLX' => '1',   //开票类型  1 正票 2 红票
            'CZDM' => '10',  //操作代码
            'HJBHSJE' => '',    //合计不含税金额
            'HJSE' => '', //合计税额
            'trade_no' => $no,  //请求流水号
            'KPHJJE' => '',   //价税合计金额
        ];
    
        return $arr;
    }
```
