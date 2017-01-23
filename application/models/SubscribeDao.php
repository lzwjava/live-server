<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/24/17
 * Time: 12:53 AM
 */
class SubscribeDao extends BaseDao
{
    function subscribeTopic($userId, $topicId)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_TOPIC_ID => $topicId
        );
        $this->db->insert(TABLE_TOPICS, $data);
        return $this->db->insert_id();
    }

    function unsubscribeTopic($userId, $topicId)
    {
        $sql = "DELETE FROM subscribes WHERE userId=? AND topicId=?";
        $binds = array($userId, $topicId);
        $this->db->query($sql, $binds);
        return $this->db->affected_rows() > 0;
    }

    function getSubscribe($userId, $topicId)
    {
        $sql = "SELECT * FROM subscribes WHERE userId=? AND topicId=?";
        $binds = array($userId, $topicId);
        return $this->db->query($sql, $binds)->row();
    }
}
