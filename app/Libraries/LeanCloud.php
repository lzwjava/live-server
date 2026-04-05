<?php

namespace App\Libraries;

/**
 * LeanCloud - LeanCloud API integration for SMS and IM
 * CI4-compatible version
 */
class LeanCloud
{
    public function curlLeanCloud(string $path, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.leancloud.cn/1.1/" . $path);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-LC-Id: " . (env('LC_PROD_APP_ID') ?: ''),
            "X-LC-Key: " . (env('LC_PROD_APP_KEY') ?: ''),
            "Content-Type: application/json",
        ]);
        if ($data === null) {
            $data = new \stdClass();
        }
        $encoded = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($result !== null && $result !== '') {
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
        return [
            "status" => $status,
            "result" => $result,
        ];
    }

    public function sendTemplateSms(string $phone, string $template, array $data): bool
    {
        $smsTemplate = defined('SMS_TEMPLATE') ? SMS_TEMPLATE : 'template';
        $keyMobilePhoneNumber = defined('KEY_MOBILE_PHONE_NUMBER') ? KEY_MOBILE_PHONE_NUMBER : 'mobilePhoneNumber';
        $data[$smsTemplate] = $template;
        $data[$keyMobilePhoneNumber] = $phone . '';

        if (ENVIRONMENT !== 'development') {
            $result = $this->curlLeanCloud("requestSmsCode", $data);
            if ($result["status"] != 200) {
                $string = json_encode($result["result"]);
                if (function_exists('logInfo')) {
                    logInfo("requestSmsCode error result: $string  data:" . json_encode($data));
                }
                return false;
            } else {
                if (function_exists('logInfo')) {
                    logInfo("send sms code succeed. data: " . json_encode($data));
                }
                return true;
            }
        } else {
            if (function_exists('logInfo')) {
                logInfo("imitate requestSmsCode data: " . json_encode($data));
            }
            return true;
        }
    }

    public function createConversation(string $name, int $userId): ?string
    {
        $data = [
            'name' => $name,
            'm'    => [$userId . ''],
            'c'    => $userId . '',
            'tr'   => true,
            'attr' => ['type' => 0],
        ];
        $result = $this->curlLeanCloud('classes/_Conversation', $data);
        if ($result['status'] < 200 || $result['status'] > 300) {
            if (function_exists('logInfo')) {
                logInfo("createConversation error " . json_encode($result));
            }
            return null;
        }
        if (function_exists('logInfo')) {
            logInfo("createConversation succeed");
        }
        return $result['result']->objectId ?? null;
    }
}
