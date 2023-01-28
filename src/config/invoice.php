<?php
/**
 * Name: 发票配置文件.
 * User: 董坤鸿
 * Date: 2020/06/23
 * Time: 14:39
 */

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
    // 是否机动车销售统一发票
    'is_vehicle' => '0',
    // 开具二手车销售统一发票才需要传
    'is_second_hand_car' => '0',
];
