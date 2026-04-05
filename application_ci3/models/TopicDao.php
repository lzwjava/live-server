<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/23/17
 * Time: 4:16 AM
 */
class TopicDao extends BaseDao
{
    function addTopic($name)
    {
        $data = array(KEY_NAME => $name);
        $this->db->insert(TABLE_TOPICS, $data);
        return $this->db->insert_id();
    }

    function getTopics()
    {
        return $this->getListFromTable(TABLE_TOPICS, '1', '1');
    }

    function topicFields()
    {
        return array(KEY_TOPIC_ID, KEY_NAME);
    }

    function topicPublicFields($prefix = TABLE_TOPICS, $alias = false)
    {
        return $this->mergeFields($this->topicFields(), $prefix, $alias);
    }

}
