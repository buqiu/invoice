<?php
/**
 * Name: 发票SDK.
 * User: Kevin
 * Date: 2022/10/12
 * Time: 13:35
 */

namespace Buqiu\Invoice;

use Exception;

class InvoiceSDK
{
    /**
     * SDK版本号
     *
     * @var string
     */
    public static string $VERSION = "2.0.0";

    /**
     * 访问授权地址
     *
     * @var string
     */
    public static string $AUTH_URL = "https://open.nuonuo.com/accessToken";

    /**
     * 文件配置
     *
     * @var array
     */
    private static array $config = [];

    /**
     * 初始化属性
     *
     * @param $config
     */
    public function __construct($config)
    {
        self::$config = $config;
    }

    /**
     * 商家应用获取accessToken
     *
     * @param int $timeOut 超时时间
     * @return object
     * @throws Exception
     */
    public function getMerchantToken(int $timeOut = 6): object
    {
        //检测必填参数
        self::checkParam(self::$config['app_key'], "AppKey不能为空");
        self::checkParam(self::$config['app_secret'], "AppSecret不能为空");

        $headers = array(
            "Content-Type: application/x-www-form-urlencoded"
        );
        $params = array(
            "client_id" => self::$config['app_key'],
            "client_secret" => self::$config['app_secret'],
            "grant_type" => "client_credentials"
        );
        $params = http_build_query($params);

        $res = self::postCurl(self::$AUTH_URL, $params, $headers, $timeOut);

        return json_decode($res);
    }

    /**
     * ISV应用获取accessToken
     *
     * @param int $timeOut 超时时间
     * @return bool|string
     * @throws Exception
     */
    public static function getISVToken(int $timeOut = 6): bool|string
    {
        //检测必填参数
        self::checkParam(self::$config['app_key'], "AppKey不能为空");
        self::checkParam(self::$config['app_secret'], "AppSecret不能为空");
        self::checkParam(self::$config['code'], "code不能为空");
        self::checkParam(self::$config['tax_num'], "tax_num不能为空");
        self::checkParam(self::$config['redirect_uri'], "redirectUri不能为空");

        $headers = array(
            "Content-Type: application/x-www-form-urlencoded"
        );
        $params = array(
            "client_id" => self::$config['app_key'],
            "client_secret" => self::$config['app_secret'],
            "code" => self::$config['code'],
            "taxNum" => self::$config['tax_num'],
            "redirect_uri" => self::$config['redirect_uri'],
            "grant_type" => "authorization_code"
        );
        $params = http_build_query($params);

        $res = self::postCurl(self::$AUTH_URL, $params, $headers, $timeOut);

        return json_decode($res);
    }

    /**
     * ISV应用刷新accessToken
     *
     * @param string $refreshToken 调用令牌
     * @param int $userId oauthUser中的userId
     * @param int $timeOut 超时时间
     * @return bool|string
     * @throws Exception
     */
    public static function refreshISVToken(string $refreshToken, int $userId, int $timeOut = 6): bool|string
    {
        self::checkParam($userId, "userId不能为空");
        self::checkParam(self::$config['app_secret'], "appSecret不能为空");
        self::checkParam($refreshToken, "refreshToken不能为空");

        $headers = array(
            "Content-Type: application/x-www-form-urlencoded"
        );
        $params = array(
            "client_id" => $userId,
            "client_secret" => self::$config['app_secret'],
            "refresh_token" => $refreshToken,
            "grant_type" => "refresh_token"
        );
        $params = http_build_query($params);

        $res = self::postCurl(self::$AUTH_URL, $params, $headers, $timeOut);

        return json_decode($res);
    }

    /**
     * 发送HTTP POST请求 <同步>
     *
     * @param string $senId sendId
     * @param string $token 授权码
     * @param string $method API名称
     * @param mixed $content 私有参数, 标准JSON格式
     * @param int $timeOut 超时时间
     * @return mixed
     * @throws Exception
     */
    public function sendPostSyncRequest(string $senId, string $token, string $method, mixed $content, int $timeOut = 6): mixed
    {
        $url = self::$config['url'];
        $appKey = self::$config['app_key'];
        $appSecret = self::$config['app_secret'];
        $taxNum = self::$config['tax_num'];

        self::checkParam($senId, "senId不能为空");
        self::checkParam($token, "token不能为空");
        self::checkParam($appKey, "appKey不能为空");
        self::checkParam($method, "method不能为空");
        self::checkParam($url, "请求地址URL不能为空");
        self::checkParam($content, "content不能为空");
        self::checkParam($appSecret, "appSecret不能为空");

        try {
            $timestamp = time();
            $nonce = rand(10000, 1000000000);

            $finalUrl = "{$url}?senid={$senId}&nonce={$nonce}&timestamp={$timestamp}&appkey={$appKey}";

            $urlInfo = parse_url($url);
            if ($urlInfo === FALSE) {
                throw new Exception("url解析失败");
            }

            $sign = self::makeSign($urlInfo["path"], $appSecret, $appKey, $senId, $nonce, $content, $timestamp);

            $headers = array(
                "Content-Type: application/json",
                "X-Nuonuo-Sign: {$sign}",
                "accessToken: {$token}",
                "userTax: {$taxNum}",
                "method: {$method}",
                "sdkVer: " . self::$VERSION
            );

            // 调用开放平台API
            return json_decode(self::postCurl($finalUrl, $content, $headers, $timeOut));
        } catch (Exception $e) {
            throw new Exception("发送HTTP请求异常:" . $e->getMessage());
        }
    }

    /**
     * 验证参数
     *
     * @param mixed $param 参数
     * @param string $errMsg 错误信息
     * @return void
     * @throws Exception
     */
    public static function checkParam(mixed $param, string $errMsg)
    {
        if(empty($param)) {
            throw new Exception($errMsg);
        }
    }

    /**
     * 以post方式发起http调用
     *
     * @param string $url  url
     * @param string $params post参数
     * @param int $second   url执行超时时间，默认30s
     * @throws Exception()
     */
    private static function postCurl(string $url, string $params, $headers = array(), int $second = 30): bool|string
    {
        $ch = curl_init();
        $curlVersion = curl_version();

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("curl出错:$error");
        }
    }

    /**
     * 计算签名
     *
     * @param string $path 请求地址
     * @param string $appSecret appSecret
     * @param string $appKey appKey
     * @param string $senId senId
     * @param string $nonce 随机码
     * @param string $body 请求包体
     * @param string $timestamp 时间戳
     * @return string
     */
    public static function MakeSign(string $path, string $appSecret, string $appKey, string $senId, string $nonce, string $body, string $timestamp): string
    {
        $pieces = explode('/', $path);
        $signStr = "a={$pieces[3]}&l={$pieces[2]}&p={$pieces[1]}&k={$appKey}&i={$senId}&n={$nonce}&t={$timestamp}&f={$body}";

        return base64_encode(hash_hmac("sha1", $signStr, $appSecret, true));
    }

    /**
     * 过滤参数
     *
     * @param array $params 参数
     * @param string $method 请求方法名
     * @return false|string
     */
    public function getBody(array $params,string $method): bool|string
    {
        switch ($method){
            case 'nuonuo.ElectronInvoice.queryInvoiceResult' :
                if(isset($params['serialNos'])){
                    $body['serialNos'] = $params['serialNos'];
                }
                if(isset($params['orderNos'])){
                    $body['orderNos'] = $params['orderNos'];
                }
                break;
            case 'nuonuo.ElectronInvoice.requestBillingNew' :
                $body =  array(
                    "order" => array(
                        "buyerName" => $params['buyerName'] ?? "", // 购方名称
                        "buyerTaxNum" => $params['buyerTaxNum'] ?? "", // 购方税号（企业要填，个人可为空；全电专票、二手车销售统一发票时必填）
                        "buyerTel" => $params['buyerTel'] ?? "", // 购方电话（购方地址+电话总共不超100字符；二手车销售统一发票时必填）
                        "buyerAddress" => $params['buyerAddress'] ?? "", // 购方地址（购方地址+电话总共不超100字符；二手车销售统一发票时必填）
                        "buyerAccount" => $params['buyerAccount'] ?? "", // 购方银行开户行及账号
                        "salerTaxNum" => self::$config['tax_num'] ?? '', // 销方税号
                        "salerTel" => self::$config['saler_tel'] ?? "", // 销方电话
                        "salerAddress" => self::$config['saler_address'] ?? "", // 销方地址
                        "salerAccount" => self::$config['saler_account'] ?? "", // 销方银行开户行及账号(二手车销售统一发票时必填)
                        "orderNo" => $params['orderNo'] ?? "", // 订单号（每个企业唯一）
                        "invoiceDate" => $params['invoiceDate'] ?? "", // 订单时间
                        "invoiceCode" => $params['invoiceCode'] ?? "", // 冲红时填写的对应蓝票发票代码（红票必填 10位或12 位， 11位的时候请左补 0）
                        "invoiceNum" => $params['invoiceNum'] ?? "", // 冲红时填写的对应蓝票发票号码（红票必填，不满8位请左补0）
                        // 冲红原因：1:销货退回;2:开票有误;3:服务中止;4:发生销售折让(开具红票时且票种为p,c,e,f,r需要传--成品油发票除外；不传时默认为 1)
                        "redReason" => $params['redReason'] ?? "1",
                        // 红字信息表编号.专票冲红时此项必填，且必须在备注中注明“开具红字增值税专用发票信息表编号ZZZZZZZZZZZZZZZ
                        // Z”字样，其 中“Z”为开具红字增值税专用发票所需要的长度为16位信息表编号（建议16位，最长可支持24位）。
                        "billInfoNo" => $params['billInfoNo'] ?? "",
                        "departmentId" => self::$config['department_id'] ?? "", // 部门门店id（诺诺系统中的id）
                        "clerkId" => self::$config['clerk_id'] ?? "", // 开票员id（诺诺系统中的id）
                        // 冲红时，在备注中注明“对应正数发票代码:XXXXXXXXX号码:YYYYYYYY”文案，其中“X”为发票代码，“Y”为发票号码，可以不填，接口会自动添加该文案；机动车发票蓝票时备注只能为空
                        "remark" => $params['remark'] ?? "",
                        "checker" => self::$config['checker'] ?? "", // 复核人
                        "payee" => self::$config['payee'] ?? "", // 收款人
                        "clerk" => self::$config['clerk'] ?? "", // 开票员（全电发票时需要传入和开票登录账号对应的开票员姓名）
                        "listFlag" => $params['listFlag'] ?? "0", // 清单标志：非清单:0；清单:1，默认:0，电票固定为0
                        "listName" => !empty($params['listFlag']) ? ($params['listName'] ?? "详见销货清单") : "", //清单项目名称：对应发票票面项目名称（listFlag为1时，必填，默认为“详见销货清单”）
                        "pushMode" => $params['pushMode'] ?? "-1", // 推送方式：-1,不推送;0,邮箱;1,手机（默认）;2,邮箱、手机
                        "buyerPhone" => $params['buyerPhone'] ?? "", // 购方手机（pushMode为1或2时，此项为必填，同时受企业资质是否必填控制）
                        "email" => $params['email'] ?? "", // 推送邮箱（pushMode为0或2时，此项为必填，同时受企业资质是否必填控制）
                        "invoiceType" => $params['invoiceType'] ?? "1", // 开票类型：1:蓝票;2:红票 （全电发票暂不支持红票）
                        // 发票种类：
                        // p,普通发票(电票)(默认);
                        // c,普通发票(纸票);
                        // s,专用发票;
                        // e,收购发票(电票);
                        // f,收购发票(纸质);
                        //r,普通发票(卷式);
                        // b,增值税电子专用发票;
                        // j,机动车销售统一发票;
                        // u,二手车销售统一发票;
                        // bs:电子发票(增值税专用发票)-即全电专票,
                        // pc:电子发票(普通发票)-即全电普票
                        "invoiceLine" => $params['invoiceLine'] ?? "p",
                        "specificFactor" => $params['specificFactor'] ?? "0", // 特定要素：0普通发票（默认） 、 1成品油、 31建安发票 、 32房地产销售发票
                        // 代开标志：0非代开;1代开。
                        // 代开蓝票时备注要求填写文案：代开企业税号:***,代开企业名称:***；
                        // 代开红票时备注要求填写文案：对应正数发票代码:***号码:***代开企业税号:***代开企业名称:***
                        "proxyInvoiceFlag" => $params['proxyInvoiceFlag'] ?? "",
                        "callBackUrl" => self::$config['call_back_url'] ?? "", // 开票回调地址
                        "extensionNumber" => $params['extensionNumber'] ?? "", // 分机号（只能为空或者数字）
                        "terminalNumber" => $params['terminalNumber'] ?? "", // 终端号（开票终端号，只能 为空或数字）
                        "machineCode" => $params['machineCode'] ?? "", // 机器编号（12位盘号）
                        "vehicleFlag" => $params['vehicleFlag'] ?? "0", // 是否机动车类专票 0-否 1-是
                        // 是否隐藏编码表版本号 0-否 1-是（默认0，在企业资质中也配置为是隐藏的时候，并且此字段传1的时候代开发票 税率显示***）
                        "hiddenBmbbbh" => $params['hiddenBmbbbh'] ?? "0",
                        // 指定开票发票代码（只有票种为：c或f时才有效，满足普票开二联、收购票开五联；nextInvoiceCode、nextInvoiceNum必须同时有值或同时为空）
                        "nextInvoiceCode" => $params['nextInvoiceCode'] ?? "",
                        // 指定开票发票号码（只有票种为：c或f时才有效，满足普票开二联、收购票开五联；nextInvoiceCode、nextInvoiceNum必须同时有值或同时为空）
                        "nextInvoiceNum" => $params['nextInvoiceNum'] ?? "",
                        // 3%、1%税率开具理由（企业为小规模/点下户时才需要），对应值：1-开具发票为2022年3月31日前发生纳税义务的业务；
                        // 2-前期已开具相应征收率发票，发生销售折让、中止或者退回等情形需要开具红字发票，或者开票有误需要重新开具；
                        // 3-因为实际经营业务需要，放弃享受免征增值税政策
                        "surveyAnswerType" => $params['surveyAnswerType'] ?? "",
                        "buyerManagerName" => $params['buyerManagerName'] ?? "", // 购买方经办人姓名（全电发票特有字段）
                        // 经办人证件类型：
                        // 101-组织机构代码证, 102-营业执照, 103-税务登记证, 199-其他单位证件, 201-居民身份证, 202-军官证,
                        // 203-武警警官证, 204-士兵证, 205-军队离退休干部证, 206-残疾人证, 207-残疾军人证（1-8级）, 208-外国护照,
                        // 210-港澳居民来往内地通行证, 212-中华人民共和国往来港澳通行证, 213-台湾居民来往大陆通行证, 214-大陆居民往来台湾通行证,
                        // 215-外国人居留证, 216-外交官证 299-其他个人证 件(全电发票特有)
                        "managerCardType" => $params['managerCardType'] ?? "201",
                        "managerCardNo" => $params['managerCardNo'] ?? "", // 经办人证件号码（全电发票特有字段）
                        "invoiceDetail" => array(
                            // 商品名称（如invoiceLineProperty =1，则此商品行为折扣行，折扣行不允许多行折扣，折扣行必须紧邻被折扣行，商品名称必须与被折扣行一致）
                            "goodsName" => $params['goodsName'] ?? "",
                            "goodsCode" => $params['goodsCode'] ?? "", // 商品编码（商品税收分类编码开发者自行填写）
                            "selfCode" => $params['selfCode'] ?? "", // 自行编码（可不填）
                            "withTaxFlag" => self::$config['with_tax_flag'] ?? "", // 单价含税标志：0:不含税,1:含税
                            // 单价（精确到小数点后8位），当单价(price)为空时，数量(num)也必须为空；
                            // (price)为空时，含税金额(taxIncludedAmount)、不含税金额(taxExcludedAmount)、税额(tax)都不能为空
                            "price" => $params['price'] ?? "",
                            "num" => $params['num'] ?? "", // 数量（精确到小数点后8位，开具红票时数量为负数）
                            "unit" => $params['unit'] ?? "", // 单位
                            "specType" => $params['specType'] ?? "", // 规格型号
                            // 税额，[不含税金额] * [税率] = [税额]；税额允许误差为 0.06。红票为负。
                            // 不含税金额、税额、含税金额任何一个不传时，会根据传入的单价，数量进行计算，可能和实际数值存在误差，建议都传入
                            "tax" => $params['tax'] ?? "",
                            // 税率，注：1、纸票清单红票存在为null的情况；2、二手车发票税率为null或者0
                            "taxRate" => $params['taxRate'] ?? "",
                            // 不含税金额。红票为负。不含税金额、税额、含税金额任何一个不传时，会根据传入的单价，数量进行计算，可能和实际数值存在误差，建议都传入
                            "taxExcludedAmount" => $params['taxExcludedAmount'] ?? "",
                            // 含税金额，[不含税金额] + [税额] = [含税金额]，红票为负。不含税金额、税额、含税金额任何一个不传时，会根据传入的单价，数量进行计算，可能和实际数值存在误差，建议都传入
                            "taxIncludedAmount" => $params['taxIncludedAmount'] ?? "",
                            "invoiceLineProperty" => $params['invoiceLineProperty'] ?? "0", // 发票行性质：0,正常行;1,折扣行;2,被折扣行，红票只有正常行
                            // 优惠政策标识：0,不使用;1,使用;
                            // 全电发票时： 01：简易征收 02：稀土产品 03：免税 04：不征税 05：先征后退 06：100%先征后退 07：50%先征后退 08：按3%简易征收 09：按5%简易征收 10：按5%
                            // 简易征收减按1.5%计征 11：即征即退30%12：即征即退50% 13：即征即退70% 14：即征即退100% 15：超税负3%即征即退16：超税负8%即征即退 17：超税负12%
                            // 即征即退 18：超税负6%即征即退
                            "favouredPolicyFlag" => $params['favouredPolicyFlag'] ?? "",
                            // 增值税特殊管理（优惠政策名称）,当favouredPolicyFlag为1时，此项必填 （全电发票时为空）
                            "favouredPolicyName" => $params['favouredPolicyName'] ?? "",
                            // 扣除额，差额征收时填写，目前只支持填写一项。 注意：当传0、空或字段不传时，都表示非差额征税；传0.00才表示差额征税：0.00 （全电发票暂不支持）
                            "deduction" => $params['deduction'] ?? "",
                            // 零税率标识：空,非零税率;1,免税;2,不征税;3,普通零税率；
                            //1、当税率为：0%，且增值税特殊管理：为“免税”， 零税率标识：需传“1”
                            //2、当税率为：0%，且增值税特殊管理：为"不征税" 零税率标识：需传“2”
                            //3、当税率为：0%，且增值税特殊管理：为空 零税率标识：需传“3”（全电发票时为空）
                            "zeroRateFlag" => $params['zeroRateFlag'] ?? "",
                            // 附加模版名称（全电发票特有字段，附加模版有值时需要添加附加要素信息列表对象，需要先在电子税局平台维护好模版）
                            "additionalElementName" => $params['additionalElementName'] ?? "",
                        ),
                        "additionalElementList" => array(
                            "elementName" => $params['elementName'] ?? "", // 信息名称（全电发票特有字段；需要与电子税局中的模版中的附加要素信息名称一致）
                            "elementType" => $params['elementType'] ?? "", // 信息类型（全电发票特有字段）
                            "elementValue" => $params['elementValue'] ?? "", // 信息值（全电发票特有字段）
                        )
                    ),
                );

                if(!empty(self::$config['is_vehicle'])){
                    // 车辆类型,同明细中商品名称，开具机动车发票时明细有且仅有一行，商品名称为车辆类型且不能为空
                    $body['order']['vehicleInfo']['vehicleType'] = $params['vehicleType'] ?? "";
                    $body['order']['vehicleInfo']['brandModel'] = $params['brandModel'] ?? ""; // 厂牌型号
                    $body['order']['vehicleInfo']['productOrigin'] = $params['productOrigin'] ?? ""; // 原产地
                    $body['order']['vehicleInfo']['certificate'] = $params['certificate'] ?? ""; // 合格证号
                    $body['order']['vehicleInfo']['importCerNum'] = $params['importCerNum'] ?? ""; // 进出口证明书号
                    $body['order']['vehicleInfo']['insOddNum'] = $params['insOddNum'] ?? ""; // 商检单号
                    $body['order']['vehicleInfo']['engineNum'] = $params['engineNum'] ?? ""; // 发动机号码
                    $body['order']['vehicleInfo']['vehicleCode'] = $params['vehicleCode'] ?? ""; // 车辆识别号码/车架号
                    $body['order']['vehicleInfo']['intactCerNum'] = $params['intactCerNum'] ?? ""; // 完税证明号码
                    $body['order']['vehicleInfo']['tonnage'] = $params['tonnage'] ?? ""; // 吨位
                    $body['order']['vehicleInfo']['maxCapacity'] = $params['maxCapacity'] ?? ""; // 限乘人数
                    // 其他证件号码/身份证号码/组织机构代码；该字段为空则为2021新版常规机动车发票，此时购方税号必填（个人在购方税号中填身份证号）；该字段有值，则为2021
                    // 新版其他证件号码的机动车发票（可以录入汉字、大写字母、数字、全角括号等，此时购方税号需要为空；用于港澳台、国外等特殊身份/税号开机动车票时使用）
                    $body['order']['vehicleInfo']['idNumOrgCode'] = $params['idNumOrgCode'] ?? "";
                    $body['order']['vehicleInfo']['manufacturerName'] = $params['manufacturerName'] ?? ""; // 生产厂家（A9开票服务器类型可支持200）
                    $body['order']['vehicleInfo']['taxOfficeName'] = $params['taxOfficeName'] ?? ""; // 主管税务机关名称（A9开票服务器类型必填）
                    $body['order']['vehicleInfo']['taxOfficeCode'] = $params['taxOfficeCode'] ?? ""; // 主管税务机关代码（A9开票服务器类型必填）
                }

                if(!empty(self::$config['is_second_hand_car'])){
                    $body['order']['secondHandCarInfo']['organizeType'] = $params['organizeType'] ?? ""; // 开票方类型 1：经营单位 2：拍卖单位 3：二手车市场
                    $body['order']['secondHandCarInfo']['vehicleType'] = $params['vehicleType'] ?? ""; // 车辆类型,同明细中商品名称，开具机动车发票时明细有且仅有一行，商品名称为车辆类型且不能为空
                    $body['order']['secondHandCarInfo']['brandModel'] = $params['brandModel'] ?? ""; // 厂牌型号
                    $body['order']['secondHandCarInfo']['vehicleCode'] = $params['vehicleCode'] ?? ""; // 车辆识别号码/车架号
                    $body['order']['secondHandCarInfo']['intactCerNum'] = $params['intactCerNum'] ?? ""; // 完税证明号码
                    $body['order']['secondHandCarInfo']['licenseNumber'] = $params['licenseNumber'] ?? ""; // 车牌照号
                    $body['order']['secondHandCarInfo']['registerCertNo'] = $params['registerCertNo'] ?? ""; // 登记证号
                    $body['order']['secondHandCarInfo']['vehicleManagementName'] = $params['vehicleManagementName'] ?? ""; // 转入地车管所名称
                    $body['order']['secondHandCarInfo']['sellerName'] = $params['sellerName'] ?? ""; // 卖方单位/个人名称（开票方类型为1、2时，必须与销方名称一致）
                    $body['order']['secondHandCarInfo']['sellerTaxnum'] = $params['sellerTaxnum'] ?? ""; // 卖方单位代码/身份证号码（开票方类型为1、2时，必须与销方税号一致）
                    $body['order']['secondHandCarInfo']['sellerAddress'] = $params['sellerAddress'] ?? ""; // 卖方单位/个人地址（开票方类型为1、2时，必须与销方地址一致）
                    $body['order']['secondHandCarInfo']['sellerPhone'] = $params['sellerPhone'] ?? ""; // 卖方单位/个人电话（开票方类型为1、2时，必须与销方电话一致）
                }

                break;
            case 'nuonuo.electronInvoice.queryCount' :
                $body['identity'] = $params['identity'] ?? "";
                $body['taxNo'] = self::$config['tax_num'] ?? "";

                break;
            default :
                $body = json_decode("{}");
                break;
        }

        return json_encode($body);
    }
}
