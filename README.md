# invoice

## desc

上海航天电子发票对接平台，PHP版本的SDK

## 安装

```
composer require buqiu/invoice
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
      $obj = new InvoiceSDK();
      $res = $obj->create($arr); //开发票
      $res = $obj->download($arr);//下载发票

        $arr['email'] = 'xxxxxxxx@qq.com'; //邮箱
        $arr['fp_dm'] = '031001900411';  //发票代码
        $arr['fp_hm'] = '90111637'; //发票号码
      $res = $obj->email($arr);//给邮箱发送发票
```





