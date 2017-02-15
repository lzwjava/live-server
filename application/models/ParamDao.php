<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 2/15/17
 * Time: 6:11 PM
 */
class ParamDao extends BaseDao
{
    function setParam($name, $value)
    {
        $this->db->where(KEY_NAME, $name);
        $this->db->update(TABLE_PARAMS, array(KEY_VALUE => $value));
        return $this->db->insert_id();
    }

    function queryParam($name)
    {
        return $this->getOneFromTable(TABLE_PARAMS, KEY_NAME, $name);
    }

    private function createParam($name, $value)
    {
        $data = array(
            KEY_NAME => $name,
            KEY_VALUE => $value
        );
        $this->db->insert(TABLE_PARAMS, $data);
        return $this->db->insert_id();
    }

    function queryOrCreateParam($name, $initValue)
    {
        $param = $this->queryParam($name);
        if (!$param) {
            $this->createParam($name, $initValue);
            return $this->queryParam($name);
        }
        return $param;
    }

    function setTaskRunning($value)
    {
        return $this->setParam(KEY_TASK_RUNNING, $value);
    }

    function queryTaskRunning()
    {
        return $this->queryOrCreateParam(KEY_TASK_RUNNING, '0');
    }


}
