<?php
require_once 'common.php';

$tpl_post = <<<EOL
<xml>
   <appid>%s</appid>
   <attach>%s</attach>
   <body>%s</body>
   <mch_id>%s</mch_id>
   <detail><![CDATA[%s]]></detail>
   <nonce_str>%s</nonce_str>
   <notify_url>%s</notify_url>
   <openid>%s</openid>
   <out_trade_no>%s</out_trade_no>
   <spbill_create_ip>%s</spbill_create_ip>
   <total_fee>%s</total_fee>
   <trade_type>%s</trade_type>
   <sign>%s</sign>
</xml>
EOL;

$gets = $_GET;

$type = trim($gets['type'])?:'payOrder';

$log = $_SERVER;
$json = '{"error":"unknown type"}';
switch ($type) {
    case 'session_key':
        $code = trim($gets['code']);
        if (empty($code)) {
            $log += array('error'=>'code is empty');
        } else {
            $tpl = 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code';
            $url = sprintf($tpl, APP_ID, APP_SECRET, $code);
            $json = file_get_contents($url);
            $log += array(
                'url' => $url
            );
        }
        
        break;
    case 'payOrder':# 下单
        $openid = trim($gets['openid']);
        
        $param = array();
        $param['appid'] = APP_ID;
        $param['attach'] = 'pay_test';
        $param['body'] = '沙宝黑的布加迪-威航一台(¥2500.00万)';
        $param['mch_id'] = MCH_ID;
        $param['detail'] = '{ "goods_detail":[ { "goods_id":"iphone6s_16G", "wxpay_goods_id":"1001", "goods_name":"iPhone6s 16G", "quantity":1, "price":528800, "goods_category":"123456", "body":"apple" }, { "goods_id":"iphone6s_32G", "wxpay_goods_id":"1002", "goods_name":"iPhone6s 32G", "quantity":1, "price":608800, "goods_category":"123789", "body":"apple iphone!" } ] }';
        $param['nonce_str'] = md5(uniqid());
        $param['notify_url'] = 'http://xxxxxxxxxxxxxxx';
        $param['openid'] = $openid;
        $param['out_trade_no'] = time();
        $param['spbill_create_ip'] = '14.23.150.211';
        $param['total_fee'] = '1';
        $param['trade_type'] = 'JSAPI';
        
         
        $sign = getSign($param);
        
        $poststr = sprintf($tpl_post,$param['appid'] , $param['attach'] , $param['body'],     $param['mch_id'] ,$param['detail'],$param['nonce_str'],   $param['notify_url'],$param['openid'], $param['out_trade_no'], $param['spbill_create_ip'] , $param['total_fee'] ,  $param['trade_type'],$sign);
       
        $log+= array('xml'=>$poststr);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $xml = request_post($url,$poststr);
        
        $resp = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)),true);
        $prepay_id = trim($resp['prepay_id']);
        $json = json_encode($resp);
       break;
    case 'requestPayment': #支付请求
        $repay_id = trim($gets['prepay_id']);
        
        $param = array();
        $param['appId'] = APP_ID;
        $param['timeStamp'] = trim(time());
        $param['nonceStr'] = '5K8264ILTKCH1xxxxxxxxxxxxx';
        $param['package'] = 'prepay_id='.$repay_id;
        $param['signType'] = 'MD5';
        
        $paySign = getSign($param);
        
        $ret['timeStamp'] =  $param['timeStamp'];
        $ret['nonceStr'] =   $param['nonceStr'];
        $ret['package'] =  $param['package'];
        $ret['signType'] = $param['signType'];
        $ret['paySign'] = $paySign;
        
        $json = json_encode($ret);
        break;
    default:
        file_put_contents('log2.txt', $json);
}

file_put_contents('log2.txt', json_encode($log ));
echo $json;