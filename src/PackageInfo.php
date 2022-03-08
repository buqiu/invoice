<?php
/**
 * Name: 发票包信息.
 * User: 董坤鸿
 * Date: 2020/06/23
 * Time: 14:19
 */

namespace Buqiu\Invoice;

class PackageInfo
{
    private static $_instance = null;

    private static $config = [];

    public function __construct($config)
    {
        self::$config = $config;
    }


    /***
     * @param $interface
     * @return string
     */
    public function getXml(string $interface, string $content)
    {
        $rand = rand(1000000000, 9999999999);
        self::$config = self::$config;

        $terminalcode = self::$config['TERMINALCODE'];
        $appid = self::$config['APPID'];
        $dsptbm = self::$config['DSPTBM'];
        $date = date('Y-m-d');
        $taxpayerid = self::$config['TAXPAYWERID'];
        $authorizationcode = self::$config['AUTHORIZATIONCODE'];
        $response = self::$config['RESPONSECODE'];
        $dataexchangeid = self::$config['DSPTBM'].$interface.date('Ymd').substr($rand, 0, 9);
        $str = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<interface xmlns="" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.chinatax.gov.cn/tirip/dataspec/interfaces.xsd"
    version="DZFP1.0">
    <globalInfo>
        <terminalCode>{$terminalcode}</terminalCode>
        <appId>{$appid}</appId>
        <version>2.0</version>
        <interfaceCode>{$interface}</interfaceCode>
        <requestCode>{$dsptbm}</requestCode>
        <requestTime>{$date}</requestTime>
        <responseCode>121</responseCode>
        <dataExchangeId>{$dataexchangeid}</dataExchangeId>
        <userName>{$dsptbm}</userName>
        <passWord/>
        <taxpayerId>{$taxpayerid}</taxpayerId>
        <authorizationCode>{$authorizationcode}</authorizationCode>
    </globalInfo>
    <returnStateInfo>
        <returnCode />
        <returnMessage />
    </returnStateInfo>
    <Data>
        <dataDescription>
            <zipCode>0</zipCode>
            <encryptCode>1</encryptCode>
            <codeType>3DES</codeType>
        </dataDescription>
        <content>
        {$content}
</content>
    </Data>
</interface>
XML;


        return $str;

    }

    /***
     * @param  array  $config
     * @param  array  $arr
     * @return string
     */
    public function getContent(array $arr)
    {

        $config = self::$config;
        $fpkj = '';
        foreach ($this->content_0($config) as $key => $item) {
            if ($item['text'] !== '') {
                $fpkj .= '<'.strtoupper($item['key']).'>'.$item['text'].'</'.$item['key'].'>';
            } else {
                $fpkj .= '<'.strtoupper($item['key']).'>'.$arr[$item['key']].'</'.$item['key'].'>';
            }
        }

        $xm_size = count($arr['items']);
        $fpkj_xm = '';
        foreach ($arr['items'] as $num => $value) {
            $fpkj_xm .= '<FPKJXX_XMXX>';
            foreach ($this->content_1($config) as $key => $item) {
                if ($item['text'] !== '') {
                    $fpkj_xm .= '<'.strtoupper($item['key']).'>'.$item['text'].'</'.$item['key'].'>';
                } else {
                    $fpkj_xm .= '<'.strtoupper($item['key']).'>'.$value[$item['key']].'</'.$item['key'].'>';
                }
            }
            $fpkj_xm .= '</FPKJXX_XMXX>';
            //津贴被折扣行
            if (isset($value['discount'])) {
                //size对应
                $xm_size++;
                $fpkj_xm .= '<FPKJXX_XMXX>';
                foreach ($this->content_1($config) as $key => $item) {
                    if ($item['text'] !== '') {
                        $fpkj_xm .= '<'.strtoupper($item['key']).'>'.$item['text'].'</'.$item['key'].'>';
                    } else {
                        $fpkj_xm .= '<'.strtoupper($item['key']).'>'.$value['discount'][$item['key']].'</'.$item['key'].'>';
                    }
                }
                $fpkj_xm .= '</FPKJXX_XMXX>';
            }
        }
        $fpkj_dd = '';
        foreach ($this->content_2() as $key => $item) {
            if ($item['text'] !== '') {
                $fpkj_dd .= '<'.strtoupper($item['key']).'>'.$item['text'].'</'.$item['key'].'>';
            } else {
                if ($item['text'] === null) {
                    $fpkj_dd .= '<'.strtoupper($item['key']).'/>';
                    continue;
                }
                $fpkj_dd .= '<'.strtoupper($item['key']).'>'.$arr[$item['key']].'</'.$item['key'].'>';
            }
        }
        $root = <<<ROOT
<REQUEST_FPKJXX class="REQUEST_FPKJXX">
    <FPKJXX_FPTXX class="FPKJXX_FPTXX">
       {$fpkj}
    </FPKJXX_FPTXX>
    <FPKJXX_XMXXS class="FPKJXX_XMXX;" size="{$xm_size}">
    {$fpkj_xm}
    </FPKJXX_XMXXS>
    <FPKJXX_DDXX class="FPKJXX_DDXX">
    {$fpkj_dd}
    </FPKJXX_DDXX>
</REQUEST_FPKJXX>
ROOT;

        return openssl_encrypt($root, "DES-EDE3", str_pad(self::$config['KEY'], 24, '0'), 0);

    }

    /***
     * @param  string  $xml
     * @return mixed|\SimpleXMLElement
     */
    public function XML2array(string $xml)
    {
        $arr = simplexml_load_string($xml);
        $arr = json_decode(json_encode($arr), true);

        return $arr;
    }

    public function getDownload($arr)
    {
        $content = '';

        foreach ($this->download(self::$config) as $key => $item) {
            if ($item['text'] !== '') {
                $content .= '<'.strtoupper($item['key']).'>'.$item['text'].'</'.$item['key'].'>';
            } else {
                if ($item['text'] === null) {
                    $content .= '<'.strtoupper($item['key']).'/>';
                    continue;
                }
                $content .= '<'.strtoupper($item['key']).'>'.$arr[$item['key']].'</'.$item['key'].'>';
            }
        }

        $root = <<<ROOT
<REQUEST_FPXXXZ_NEW class="REQUEST_FPXXXZ_NEW">
    {$content}
</REQUEST_FPXXXZ_NEW>
ROOT;

        return openssl_encrypt($root, "DES-EDE3", str_pad(self::$config['KEY'], 24, '0'), 0);
    }

    /***
     * @param  array  $arr
     * @return string
     */
    public function getEmail(array $arr)
    {
        $nsrsbh = self::$config['NSRSBH'];

        $root = <<<ROOT
<REQUEST_EMAILPHONEFPTS class="REQUEST_EMAILPHONEFPTS">
    <TSFSXX class="TSFSXX">
        <COMMON_NODES class="COMMON_NODE;" size="4">
            <COMMON_NODE>
                <NAME>TSFS</NAME>
                <VALUE>0</VALUE>
            </COMMON_NODE>
            <COMMON_NODE>
                <NAME>SJ</NAME>
                <VALUE></VALUE>
            </COMMON_NODE>
            <COMMON_NODE>
                <NAME>EMAIL</NAME>
                <VALUE>{$arr['email']}</VALUE>
            </COMMON_NODE>
            <COMMON_NODE>
                <NAME>扩展字段名称</NAME>
                <VALUE>扩展字段值</VALUE>
            </COMMON_NODE>
        </COMMON_NODES>
    </TSFSXX>
    <FPXXS class="FPXX;" size="1">
        <FPXX>
             <COMMON_NODES class="COMMON_NODE;" size="5">
                <COMMON_NODE>
                    <NAME>FPQQLSH</NAME>
                    <VALUE>{$arr['FPQQLSH']}</VALUE>
                </COMMON_NODE>
                <COMMON_NODE>
                    <NAME>NSRSBH</NAME>
                    <VALUE>{$nsrsbh}</VALUE>
                </COMMON_NODE>
                <COMMON_NODE>
                    <NAME>FP_DM</NAME>
                    <VALUE>{$arr['fp_dm']}</VALUE>
                </COMMON_NODE>
                <COMMON_NODE>
                    <NAME>FP_HM</NAME>
                    <VALUE>{$arr['fp_hm']}</VALUE>
                </COMMON_NODE>
                <COMMON_NODE>
                    <NAME>扩展字段名称</NAME>
                    <VALUE>扩展字段值</VALUE>
                </COMMON_NODE>
            </COMMON_NODES>
        </FPXX>
    </FPXXS>
</REQUEST_EMAILPHONEFPTS>
ROOT;

        return openssl_encrypt($root, "DES-EDE3", str_pad(self::$config['KEY'], 24, '0'), 0);
    }

    /***
     * @param $config
     * @return array
     */
    private function content_0($config)
    {
        return [
            'FPQQLSH' => [
                'key' => 'FPQQLSH',
                'text' => '',
                'comment' => '请求流水号',
            ],
            'DSPTBM' => [
                'key' => 'DSPTBM',
                'text' => $config['DSPTBM'],
                'comment' => '平台编码',
            ],
            'NSRSBH' => [
                'key' => 'NSRSBH',
                'text' => $config['NSRSBH'],
                'comment' => '开票方识别号',
            ],
            'NSRMC' => [
                'key' => 'NSRMC',
                'text' => $config['NSRMC'],
                'comment' => '开票方名称',
            ],
            'DKBZ' => [
                'key' => 'DKBZ',
                'text' => '0',
            ],
            'KPXM' => [
                'key' => 'KPXM',
                'text' => '',
                'comment' => '商品信息中第一条',
            ],
            'BMB_BBH' => [
                'key' => 'BMB_BBH',
                'text' => '1.0',
            ],
            'XHF_NSRSBH' => [
                'key' => 'XHF_NSRSBH',
                'text' => $config['NSRSBH'],
                'comment' => '销方识别码',
            ],
            'XHFMC' => [
                'key' => 'XHFMC',
                'text' => $config['NSRMC'],
                'comment' => '销方名称',
            ],
            'XHF_DZ' => [
                'key' => 'XHF_DZ',
                'text' => $config['XHF_DZ'],
                'comment' => '销方地址',
            ],
            'XHF_DH' => [
                'key' => 'XHF_DH',
                'text' => $config['XHF_DH'],
                'comment' => '销方电话',
            ],
            'XHF_YHZH' => [
                'key' => 'XHF_YHZH',
                'text' => $config['XHF_YHZH'],
                'comment' => '销方银行账号',
            ],
            'GHFMC' => [
                'key' => 'GHFMC',
                'text' => '',
                'comment' => '购货方名称',
            ],
            'GHF_SJ' => [
                'key' => 'GHF_SJ',
                'text' => '',
                'comment' => '购货方手机',
            ],
            'GHF_NSRSBH' => [
                'key' => 'GHF_NSRSBH',
                'text' => '',
                'comment' => '购货方识别号',
            ],
            //01-企业 02-机关事业单位 03-个人  04-其他
            'GHFQYLX' => [
                'key' => 'GHFQYLX',
                'text' => '',
                'comment' => '购货方名称',
            ],
            'GHF_DZ' => [
                'key' => 'GHF_DZ',
                'text' => '',
                'comment' => '购货方地址',
            ],
            'GHF_YHZH' => [
                'key' => 'GHF_YHZH',
                'text' => '',
                'comment' => '购货方银行、账号',
            ],
            'GHF_GDDH' => [
                'key' => 'GHF_GDDH',
                'text' => '',
                'comment' => '购货方固定电话',
            ],
            'SKY' => [
                'key' => 'SKY',
                'text' => $config['SKY'],
            ],
            'KPY' => [
                'key' => 'KPY',
                'text' => $config['KPY'],
            ],
            'FHR' => [
                'key' => 'FHR',
                'text' => $config['FHR'],
            ],
            //1 正票  2 红票
            'KPLX' => [
                'key' => 'KPLX',
                'text' => '',
                'comment' => '开票类型',
            ],
            //10 正票正常开具 11 正票错票重开 20 退货折让红票 21 错票重开红票 22 换票冲红（全冲红电子发票,开具纸质发票）
            'CZDM' => [
                'key' => 'CZDM',
                'text' => '',
                'comment' => '操作代码',
            ],
            'QD_BZ' => [
                'key' => 'QD_BZ',
                'text' => '0',
            ],
            //小数点后2位 以元为单位精确到分  double
            'KPHJJE' => [
                'key' => 'KPHJJE',
                'text' => '',
                'comment' => '价税合计金额',
            ],
            //double
            'HJBHSJE' => [
                'key' => 'HJBHSJE',
                'text' => '',
                'comment' => '合计不含税金额',
            ],
            'HJSE' => [
                'key' => 'HJSE',
                'text' => '',
                'comment' => '合计税额',
            ],
            // 发票代码
            'YFP_DM' => [
                'key' => 'YFP_DM',
                'text' => '',
                'comment' => '原发票代码',
            ],
            // 发票号码
            'YFP_HM' => [
                'key' => 'YFP_HM',
                'text' => '',
                'comment' => '原发票号码',
            ],
            'BZ' => [
                'key' => 'BZ',
                'text' => '',
                'comment' => '备注',
            ],
        ];
    }


    /***
     * @param  array  $config
     * @return array
     */
    private function content_1(array $config)
    {
        return [
            'XMMC' => [
                'key' => 'XMMC',
                'text' => '',
                'comment' => '项目名称',
            ],
            'XMSL' => [
                'key' => 'XMSL',
                'text' => '',
                'comment' => '项目数量',
            ],
            'HSBZ' => [
                'key' => 'HSBZ',
                'text' => '',
                'comment' => '含税标志',
            ],
            'FPHXZ' => [
                'key' => 'FPHXZ',
                'text' => '',
            ],
            //小数点后8位小数
            'XMDJ' => [
                'key' => 'XMDJ',
                'text' => '',
            ],
            'SPBM' => [
                'key' => 'SPBM',
                'text' => '',
            ],
            'ZXBM' => [
                'key' => 'ZXBM',
                'text' => '',
            ],
            'YHZCBS' => [
                'key' => 'YHZCBS',
                'text' => '',
                'comment' => '优惠政策标识',
            ],
            //小数点后2位
            'XMJE' => [
                'key' => 'XMJE',
                'text' => '',
                'comment' => '项目金额',
            ],
            //税率
            'SL' => [
                'key' => 'SL',
                'text' => '',
                'comment' => '税率',
            ],
            //增值税特殊管理
            'ZZSTSGL' => [
                'key' => 'ZZSTSGL',
                'text' => '',
                'comment' => '增值税特殊管理',
            ],
            //零税率标识
            'LSLBS' => [
                'key' => 'LSLBS',
                'text' => '',
                'comment' => '零税率标识',
            ],
        ];
    }

    /***
     * @return array
     */
    private function content_2()
    {
        return [
            'DDH' => [
                'key' => 'DDH',
                'text' => '',
            ],
            'DDDATE' => [
                'key' => 'DDDATE',
                'text' => null,
            ],
        ];
    }

    /***
     * @param  array  $config
     * @return array
     */
    private function download(array $config)
    {
        return [
            'DDH' => [
                'key' => 'DDH',
                'text' => '',
            ],
            'FPQQLSH' => [
                'key' => 'FPQQLSH',
                'text' => '',
            ],
            'DSPTBM' => [
                'key' => 'DSPTBM',
                'text' => $config['DSPTBM'],
            ],
            'NSRSBH' => [
                'key' => 'NSRSBH',
                'text' => $config['NSRSBH'],
            ],
            'PDF_XZFS' => [
                'key' => 'PDF_XZFS',
                'text' => '3'  //0-发票状态查询 1-pdf文件
            ],
        ];
    }

    /***
     * @param  array  $config
     * @return array
     */
    public function email(array $config)
    {
        return [
            'TSFS' => '',
            'EMAIL' => '',
            'FPQQLSH' => '',
            'NSRSBH' => $config['NSRSBH'],
            'FP_DM' => '',
            'FP_HM' => '',
        ];
    }

    /**
     * @param  null  $utimestamp
     * @return float
     */
    private function udate($utimestamp = null)
    {
        if (is_null($utimestamp)) {
            $utimestamp = microtime(true);
        }

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 100);

        return $milliseconds;
    }

    /**
     * @return false|string
     */
    public function getInvoiceNumber()
    {
        $config = self::$config;
        $root = <<<ROOT
<REQUEST_KYFPSL class="REQUEST_KYFPSL">
    <NSRSBH>{$config['NSRSBH']}</NSRSBH>
</REQUEST_KYFPSL>
ROOT;

        return openssl_encrypt($root, "DES-EDE3", str_pad(self::$config['KEY'], 24, '0'), 0);
    }
}
