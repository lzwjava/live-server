<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/23/17
 * Time: 4:16 AM
 */
class TopicDao extends BaseDao
{
    protected $table = 'topics';

    function addTopic($name)
    {
        $data = array(KEY_NAME => $name);
        $this->db->table(TABLE_TOPICS)->insert($data);
        return $this->db->insertID();
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

// Namespace bridge: allow App\Libraries\TopicDao → App\Models\TopicDao
class_alias('App\Models\TopicDao', 'App\Libraries\TopicDao');
