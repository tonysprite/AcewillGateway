<?php
namespace AcewillGateway\Handle;
class Base
{

    private $_appId="";
    private $_appKey="";
    private $_version='2.0';
    private $_ts=0;
    public function __construct($appId, $appKey)
    {
        $this->_appId=$appId;
        $this->_appKey=$appKey;
        $this->_ts=time();
    }

    /**
     * authentication
     * 签名检查
     *
     * @param  mixed $request
     * @return void
     */
    protected function getSig($args)
    {
        //参数KEY排序
        ksort($args);
        $flg = array_walk($args, function (&$item) {
            if (!empty($item) && is_array($item)) {
                ksort($item);
                array_walk($item, function (&$item2) {
                    if (!empty($item2) && is_array($item2)) {
                        ksort($item2);
                    }
                });
            }
        });

        $args['appid'] = $this->_appId;
        //获取appkey
        $args['appkey'] = $this->_appKey;
        $args['v'] = $this->_version;
        $args['ts'] = $this->_ts;
        //构造查询字符串
        $query = http_build_query($args);
        $query = preg_replace('/appid=.*?&/i', 'appid=' . $this->_appId . '&', $query);
        return md5($query);
    }
    
    public function postData($url, $req)
    {
        $data['appid']=$this->_appId;
        $data['v']=$this->_version;
        $data['ts']=$this->_ts;
        $data['sig']=$this->getSig($req);
        $data['req']=json_encode($req);
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            "Content-Type: multipart/form-data"
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}
