<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/31/16
 * Time: 11:12 PM
 */
class Packets extends BaseController
{
    public $pay;
    public $snsUserDao;
    public $userPacketDao;
    public $packetDao;
    public $userDao;
    public $weChatPlatform;

    function __construct()
    {
        parent::__construct();
        $this->load->library(Pay::class);
        $this->pay = new Pay();
        $this->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
        $this->load->model(UserPacketDao::class);
        $this->userPacketDao = new UserPacketDao();
        $this->load->model(PacketDao::class);
        $this->packetDao = new PacketDao();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
        $this->load->library(WeChatPlatform::class);
        $this->weChatPlatform = new WeChatPlatform();
    }

    protected function checkIfPacketAmountWrong($amount)
    {
        if (is_int($amount) == false) {
            $this->failure(ERROR_AMOUNT_UNIT);
            return true;
        }
        if ($amount < LEAST_COMMON_PACKET) {
            $this->failure(ERROR_PACKET_TOO_LITTLE);
            return true;
        }
        if ($amount > MAX_COMMON_PACKET) {
            $this->failure(ERROR_PACKET_TOO_MUCH);
            return true;
        }
        return false;
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_TOTAL_AMOUNT,
            KEY_CHANNEL, KEY_TOTAL_COUNT, KEY_WISHING))
        ) {
            return;
        }
        $totalAmount = $this->post(KEY_TOTAL_AMOUNT);
        $totalCount = $this->post(KEY_TOTAL_COUNT);
        $channel = $this->post(KEY_CHANNEL);
        $wishing = $this->post(KEY_WISHING);
        $totalAmount = $this->toNumber($totalAmount);
        if ($this->checkIfPacketAmountWrong($totalAmount)) {
            return;
        }
        if ($totalAmount < $totalCount * 100) {
            $this->failure(ERROR_PACKET_AT_LEAST);
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($this->checkIfNotInArray($channel, array(CHANNEL_ALIPAY_APP,
            CHANNEL_WECHAT_QRCODE, CHANNEL_WECHAT_H5))
        ) {
            return;
        }
        $metaData = array(KEY_TYPE => CHARGE_TYPE_PACKET,
            KEY_TOTAL_AMOUNT => $totalAmount, KEY_USER_ID => $user->userId,
            KEY_TOTAL_COUNT => $totalCount, KEY_WISHING => $wishing);
        $subject = '发红包';
        $body = '发红包';

        $openId = null;

        if ($channel == CHANNEL_WECHAT_H5) {
            $snsUser = $this->snsUserDao->getSnsUserByUser($user);
            if (!$snsUser) {
                $this->failure(ERROR_MUST_BIND_WECHAT);
                return;
            }
            $openId = $snsUser->openId;
        }

        $ch = $this->pay->createChargeAndInsert($totalAmount, $channel, $subject, $body,
            $metaData, $user, $openId);
        if (!$ch) {
            $this->failure(ERROR_CHARGE_CREATE);
            return;
        }
        $this->succeed($ch);
    }

    private function calRandomAmount($packet)
    {
        if ($packet->remainCount == 1) {
            return $packet->balance;
        } else {
            $realMax = $packet->balance - ($packet->remainCount - 1) * 100;
            $idealMax = floor($packet->balance / $packet->remainCount) * 2;
            $max = $idealMax;
            if ($max > $realMax) {
                $max = $realMax;
            }
            $min = 100;
            if ($max < 100) {
                return 100;
            } else {
                return mt_rand(0, $max - $min) + $min;
            }
        }
    }

    function grab_get($packetId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $packet = $this->packetDao->getPacketById($packetId);
        if ($this->checkIfObjectNotExists($packet)) {
            return;
        }
        if ($packet->remainCount <= 0) {
            $this->succeed(array('status' => false));
            return;
        }
        $userPacket = $this->userPacketDao->getUserPacket($user->userId, $packet->packetId);
        if ($userPacket) {
            $this->failure(ERROR_ALREADY_GRAB);
            return;
        }
        $snsUser = $this->snsUserDao->getSnsUserByUser($user);
        if (!$snsUser) {
            $this->failure(ERROR_MUST_BIND_WECHAT);
            return;
        }
        $openId = $snsUser->openId;
        $cnt = 0;
        for (; ;) {
            ++$cnt;
            $packet = $this->packetDao->getPacketById($packetId);
            if ($cnt > 1000 || $packet->remainCount <= 0) {
                $this->succeed(array('status' => false));
                break;
            }
            $amount = $this->calRandomAmount($packet);
            $this->db->trans_begin();
            $balance = $packet->balance - $amount;
            $updated = $this->packetDao->updatePacket($packetId, $balance, $packet->remainCount);
            if ($updated) {
                $userPacketId = $this->userPacketDao->addUserPacket($user->userId, $packetId, $amount);
                if (!$userPacketId) {
                    $this->failure(ERROR_SQL_WRONG);
                    $this->db->trans_rollback();
                    break;
                }
                $sender = $this->userDao->findUserById($packet->userId);
                list($ok, $data) = $this->pay->sendRedPacket($openId, $sender->username, $amount, $packet->wishing);
                if (!$ok) {
                    $this->failure(ERROR_PACKET_SEND, $data . ' 请使用一个常用的微信');
                    $this->db->trans_rollback();
                    return;
                }
                $ok = $this->userPacketDao->sendSucceed($userPacketId);
                if (!$ok) {
                    $this->failure(ERROR_SQL_WRONG);
                    $this->db->trans_rollback();
                    return;
                }

                $this->weChatPlatform->notifyOwnerByUserPacket($user->userId,
                    $packet->userId, $packetId, $amount, $packet->wishing, true);

                $this->db->trans_commit();
                $this->succeed(array('status' => true));
                break;
            }
        }
    }

    function myPacket_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $packet = $this->packetDao->getMyPacket($user->userId);
        $this->succeed($packet);
    }

    function one_get($packetId)
    {
        $packet = $this->packetDao->getPacketById($packetId);
        $this->succeed($packet);
    }

    function meAll_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $packets = $this->packetDao->getMyPackets($user->userId);
        $this->succeed($packets);
    }

    function allPacketsById_get($packetId)
    {
        $userPackets = $this->userPacketDao->getUserPackets($packetId);
        $this->succeed($userPackets);
    }
}