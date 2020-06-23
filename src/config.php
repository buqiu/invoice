<?php
/**
 * Name: 发票配置文件.
 * User: 董坤鸿
 * Date: 2020/06/23
 * Time: 14:39
 */
namespace invoice\src;


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

