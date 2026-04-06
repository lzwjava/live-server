<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/31/16
 * Time: 3:04 PM
 */
class ApplicationDao extends BaseDao
{
    protected $table = 'applications';

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
        $this->db->table(TABLE_APPLICATIONS)->insert($data);
        return $this->db->insertID();
    }

    function updateData($applicationId, $data)
    {
        return $this->db->table(TABLE_APPLICATIONS)->where(KEY_APPLICATION_ID, $applicationId)->update($data) !== false;
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
// Namespace bridge: allow App\Libraries\ApplicationDao → App\Models\ApplicationDao
class_alias('App\Models\ApplicationDao', 'App\Libraries\ApplicationDao');
