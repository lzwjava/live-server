<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/1/22
 * Time: 上午12:15
 */
class LeanCloud
{
    function curlLeanCloud($path, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.leancloud.cn/1.1/" . $path);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "X-LC-Id: " . LC_APP_ID,
            "X-LC-Key: " . LC_APP_KEY,
            "Content-Type: application/json"
        ));
        if ($data == null) {
            $data = new stdClass();
        }
        $encoded = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($result != null || $result != '') {
            $result = json_decode($result);
        }
        if ($status < 200 || $status >= 300) {
            if ($result && isset($result->error)) {
                $result = $result->error;
            }
        }
        if ($result === false) {
            $result = 'Network error when curl LeanCloud';
        }
        return array(
            "status" => $status,
            "result" => $result
        );
    }

    function sendTemplateSms($phone, $template, $data)
    {
        $data[SMS_TEMPLATE] = $template;
        $data[KEY_MOBILE_PHONE_NUMBER] = $phone . '';
        if (ENVIRONMENT != 'development') {
            $result = $this->curlLeanCloud("requestSmsCode", $data);
            if ($result["status"] != 200) {
                $string = json_encode($result["result"]);
                logInfo("requestSmsCode error result: $string  data:" . json_encode($data));
                return false;
            } else {
                logInfo("send sms code succeed. data: " . json_encode($data));
                return true;
            }
        } else {
            logInfo("imitate requestSmsCode data: " . json_encode($data));
            return true;
        }
    }

    function createConversation($name, $userId)
    {
        $data = array('name' => $name, 'm' => array($userId . ''), 'c' => $userId . '',
            'tr' => true, 'attr' => array('type' => 0));
        $result = $this->curlLeanCloud('classes/_Conversation', $data);
        if ($result['status'] < 200 | $result['status'] > 300) {
            logInfo("createConversation error " . json_encode($result));
            return null;
        } else {
            logInfo("createConversation succeed");
            return $result['result']->objectId;
        }
    }

}
