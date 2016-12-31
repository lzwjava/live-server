<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/31/16
 * Time: 3:04 PM
 */
class ApplicationDao extends BaseDao
{
    function create($userId, $name, $wechatAccount, $socialAccount, $introduction)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_NAME => $name,
            KEY_WECHAT_ACCOUNT => $wechatAccount,
            KEY_SOCIAL_ACCOUNT => $socialAccount,
            KEY_INTRODUCTION => $introduction,
            KEY_STATUS => APPLICATION_STATUS_REVIEWING
        );
        $this->db->insert(TABLE_APPLICATIONS, $data);
        return $this->db->insert_id();
    }

    function updateData($applicationId, $data)
    {
        $this->db->where(KEY_APPLICATION_ID, $applicationId);
        $this->db->update(TABLE_APPLICATIONS, $data);
        return $this->db->affected_rows() > 0;
    }

    function getApplication($applicationId)
    {
        return $this->getOneFromTable(TABLE_APPLICATIONS, KEY_APPLICATION_ID, $applicationId);
    }

    function getApplicationByUserId($userId)
    {
        return $this->getOneFromTable(TABLE_APPLICATIONS, KEY_USER_ID, $userId);
    }

    function setReviewSucceed($applicationId)
    {
        $data = array(KEY_STATUS => APPLICATION_STATUS_SUCCEED);
        return $this->updateData($applicationId, $data);
    }

    function setReviewReject($applicationId, $remark)
    {
        $data = array(
            KEY_STATUS => APPLICATION_STATUS_REJECT,
            KEY_REVIEW_REMARK => $remark
        );
        return $this->updateData($applicationId, $data);
    }

    function setReviewing($applicationId)
    {
        $data = array(KEY_STATUS => APPLICATION_STATUS_REVIEWING);
        return $this->updateData($applicationId, $data);
    }

    function setReviewNotified($applicationId)
    {
        $data = array(
            KEY_REVIEW_NOTIFIED => 1,
        );
        return $this->updateData($applicationId, $data);
    }


}