<?php
use Endroid\QrCode\QrCode;
/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 7/28/16
 * Time: 12:46 AM
 */
class Lives extends BaseController
{
    public $liveDao;
    public $statusDao;
    public $sms;
    public $attendanceDao;
    public $weChatPlatform;
    public $couponDao;
    public $videoDao;
    public $chargeDao;
    public $weChatAppClient;
    public $qiniuLive;
    public $jobDao;
    public $jobHelperDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(StatusDao::class);
        $this->statusDao = new StatusDao();
        $this->load->library(Sms::class);
        $this->sms = new Sms();
        $this->load->model(AttendanceDao::class);
        $this->attendanceDao = new AttendanceDao();
        $this->load->library(WeChatPlatform::class);
        $this->weChatPlatform = new WeChatPlatform();
        $this->load->model(CouponDao::class);
        $this->couponDao = new CouponDao();
        $this->load->model(VideoDao::class);
        $this->videoDao = new VideoDao();
        $this->load->model(ChargeDao::class);
        $this->chargeDao = new ChargeDao();
        $this->load->library(WeChatAppClient::class);
        $this->weChatAppClient = new WeChatAppClient();
        $this->load->library(QiniuLive::class);
        $this->qiniuLive = new QiniuLive();
        $this->load->model(JobDao::class);
        $this->jobDao = new JobDao();
        $this->load->model(JobHelperDao::class);
        $this->jobHelperDao = new JobHelperDao();
    }

    protected function checkIfAmountWrong($amount)
    {
        if (is_int($amount) == false) {
            $this->failure(ERROR_AMOUNT_UNIT);
            return true;
        }
        if ($amount < LEAST_COMMON_PAY) {
            $this->failure(ERROR_AMOUNT_TOO_LITTLE);
            return true;
        }
        if ($amount > MAX_COMMON_PAY) {
            $this->failure(ERROR_AMOUNT_TOO_MUCH);
            return true;
        }
        return false;
    }

    function begin_get($liveId)
    {
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($live->status != LIVE_STATUS_WAIT) {
            $this->failure(ERROR_LIVE_NOT_WAIT);
            return;
        }
        $this->statusDao->open($liveId);
        $ok = $this->liveDao->beginLive($liveId);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed($ok);
    }

    function create_post()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $liveId = $this->liveDao->createLive($user->userId, $user->username . '的直播');
        if (!$liveId) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $live = $this->liveDao->getLive($liveId, $user);
        $this->succeed($live);
    }

    function update_post($liveId)
    {
        $keys = array(KEY_SUBJECT, KEY_COVER_URL, KEY_COURSEWARE_KEY, KEY_LIVE_QRCODE_KEY,
            KEY_AMOUNT, KEY_DETAIL, KEY_PLAN_TS, KEY_PREVIEW_URL, KEY_SPEAKER_INTRO,
            KEY_NEED_PAY, KEY_NOTICE, KEY_SHARE_ICON);
        if ($this->checkIfNotAtLeastOneParam($this->post(), $keys)
        ) {
            return;
        }
        $data = $this->postParams($keys);
        if (isset($data[KEY_NEED_PAY])) {

        }

        if (isset($data[KEY_AMOUNT])) {
            $data[KEY_AMOUNT] = $this->toNumber($data[KEY_AMOUNT]);
            if (isset($data[KEY_NEED_PAY])) {
                $needPay = $this->toNumber($data[KEY_NEED_PAY]);
                if ($needPay) {
                    if ($this->checkIfAmountWrong($data[KEY_AMOUNT])) {
                        return;
                    }
                }
            }
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($user->userId != $live->ownerId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        $originPlanTs = $live->planTs;
        $this->liveDao->update($liveId, $data);

        if (isset($data[KEY_PLAN_TS])) {
            $newPlanTs = $data[KEY_PLAN_TS];
            $newLive = $this->liveDao->getLive($liveId);
            if ($newPlanTs != $originPlanTs && $newLive->status >= LIVE_STATUS_WAIT) {
                $this->jobDao->insertNotifyJobs($newLive);
            }
        }

        $this->succeed();
    }

    function updateTopic_post($liveId)
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_OP))) {
            return;
        }
        $topicId = $this->post(KEY_TOPIC_ID);
        $op = $this->post(KEY_OP);
        if ($op == OP_ADD) {
            if (!$topicId) {
                $this->failureOfParam(KEY_TOPIC_ID);
                return;
            }
        }
        if ($this->checkIfNotInArray($op, array(OP_ADD, OP_DEL))) {
            return;
        }
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($op == OP_ADD) {
            $this->liveDao->updateTopic($liveId, $topicId);
            $this->succeed();
        } else if ($op == OP_DEL) {
            $this->liveDao->removeTopic($liveId);
            $this->succeed();
        } else {
            $this->failure(ERROR_PARAMETER_ILLEGAL);
        }
    }

    function list_get()
    {
        $skip = $this->skip();
        $limit = $this->limit();
        $user = $this->getSessionUser();
        $lives = $this->liveDao->getHomeLives($skip, $limit, $user);
        $this->succeed($lives);
    }

    function recommend_get()
    {
        $skip = $this->skip();
        $limit = $this->limit();
        $skipLiveId = $this->get(KEY_SKIP_LIVE_ID);
        if (!$skipLiveId) {
            $skipLiveId = 0;
        }
        $user = $this->getSessionUser();
        $lives = $this->liveDao->getRecommendLives($skip, $limit, $user, $skipLiveId);
        $this->succeed($lives);
    }

    function one_get($id)
    {
        $user = $this->getSessionUser();
        $live = $this->liveDao->getLive($id, $user);
        $this->succeed($live);
    }

    function alive_get($id)
    {
        $ok = $this->statusDao->alive($id);
        if (!$ok) {
            $this->failure(ERROR_ALIVE_FAIL);
            return;
        }
        $this->succeed($ok);
    }

    function end_get($id)
    {
        $live = $this->liveDao->getLive($id);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($live->ownerId != $user->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        if ($live->status != LIVE_STATUS_ON) {
            $this->failure(ERROR_LIVE_NOT_START);
            return;
        }
        $ok = $this->statusDao->setLiveTranscode($id);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed($ok);
    }

    function finish_get($id)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->getLive($id, $user);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($live->ownerId != $user->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        if ($live->status != LIVE_STATUS_ON && $live->status != LIVE_STATUS_TRANSCODE) {
            $this->failure(ERROR_LIVE_NOT_START);
            return;
        }
        $url = $this->qiniuLive->getPlaybackUrl($live);
        if (!$url) {
            $this->failure(ERROR_PLAYBACK_FAIL);
            return;
        }
        $this->db->trans_begin();
        $endOk = $this->liveDao->endLive($id);
        $ok = $this->videoDao->addVideoByLive($live);
        if (!$endOk || !$ok) {
            $this->db->trans_rollback();
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->db->trans_commit();
        $this->succeed();
    }

    function submitReview_get($id)
    {
        $live = $this->liveDao->getLive($id);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if (!$live->coverUrl || !trim($live->subject) || !trim($live->detail)) {
            $this->failure(ERROR_FIELDS_EMPTY);
            return;
        }
        if ($live->needPay && $this->checkIfAmountWrong($live->amount)) {
            return;
        }
        if (mb_strlen($live->detail) < 100) {
            $this->failure(ERROR_DETAIL_TOO_SHORT);
            return;
        }
        if (isTimeBeforeNow($live->planTs)) {
            $this->failure(ERROR_PLAN_TS_INVALID);
            return;
        }
        if ($live->status >= LIVE_STATUS_REVIEW) {
            $this->failure(ERROR_ALREADY_REVIEW);
            return;
        }
        if (mb_strlen($live->speakerIntro) < 50) {
            $this->failure(ERROR_SPEAKER_INTRO_TOO_SHORT);
            return;
        }
        $ok = $this->liveDao->setLiveReview($id);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->weChatPlatform->notifyNewReview($live);
        $this->succeed(true);
    }

    function publish_get($id)
    {
        $live = $this->liveDao->getLive($id);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($this->checkIfNotAdmin()) {
            return;
        }
        if ($live->status != LIVE_STATUS_REVIEW) {
            $this->failure(ERROR_LIVE_NOT_REVIEW);
            return;
        }
        $this->db->trans_begin();
        $liveOk = $this->liveDao->setLivePrepare($id);
        $jobOk = $this->jobDao->insertNotifyJobs($live);
        if (!$liveOk || !$jobOk) {
            $this->db->trans_rollback();
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->db->trans_commit();
        $this->succeed();
    }

    function setWait_get($id)
    {
        $live = $this->liveDao->getLive($id);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($live->ownerId != $user->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        if ($live->status != LIVE_STATUS_ON) {
            $this->failure(ERROR_LIVE_NOT_ON);
            return;
        }
        $this->liveDao->setLivePrepare($id);
        $this->succeed();
    }

    function setReview_get($id)
    {
        $live = $this->liveDao->getLive($id);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($live->ownerId != $user->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        if ($live->status != LIVE_STATUS_WAIT) {
            $this->failure(ERROR_LIVE_NOT_WAIT);
            return;
        }
        $this->liveDao->setLiveReview($id);
        $this->succeed();
    }

    function lastPrepare_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->lastPrepareLive($user);
        if (!$live) {
            $this->failure(ERROR_CREATE_LIVE);
            return;
        }
        $this->succeed($live);
    }

    function attended_get()
    {
        $userId = $this->get(KEY_USER_ID);
        $curUser = $this->getSessionUser();
        if (!$userId) {
            $userId = $curUser->userId;
        }
        $lvs = $this->liveDao->getAttendedLivesOfUser($userId, $curUser);
        $this->succeed($lvs);
    }

    function my_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $lvs = $this->liveDao->getLivesOfUser($user->userId, $user);
        $this->succeed($lvs);
    }

    function userLives_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_USER_ID))) {
            return;
        }
        $userId = $this->get(KEY_USER_ID);
        $user = $this->getSessionUser();
        $lvs = $this->liveDao->getLivesOfUser($userId, $user);
        $this->succeed($lvs);
    }

    function attendedUsers_get($liveId)
    {
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $skip = $this->skip();
        $limit = $this->limit();
        $users = $this->liveDao->getAttendedUsers($liveId, $skip, $limit);
        $this->succeed($users);
    }

    private function findUser($toUser, $users)
    {
        foreach ($users as $user) {
            if ($user->userId == $toUser->userId) {
                return $user;
            }
        }
        return null;
    }

    function notifyLiveStartRecommend_get($liveId)
    {
        if ($this->checkIfParamsNotExist($this->get(), array('relatedLiveId'))) {

        }
        $relatedLiveId = $this->get('relatedLiveId');
        $live = $this->liveDao->getLive($liveId);
        $realtedLive = $this->liveDao->getLive($relatedLiveId);

        $users = $this->liveDao->getAttendedUsers($liveId, 0, 1000000);
        $relatedUsers = $this->liveDao->getAttendedUsers($relatedLiveId, 0, 100000);
        $succeedCount = 0;
        foreach ($relatedUsers as $relatedUser) {
            $theUser = $this->findUser($relatedUser, $users);
            if ($theUser) {
            } else {
                $this->weChatPlatform->notifyUserByWeChat($relatedUser->userId, $live);
                $succeedCount++;
            }
        }
        logInfo("finished " . $succeedCount . " total " . count($relatedUsers));
        $this->succeed(array('succeedCount' => $succeedCount, 'total' => count($relatedUsers)));
    }

    function notifyLiveStart_get($liveId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($live->ownerId != $user->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        $oneHour = $this->toNumber($this->get('oneHour'));
        if ($oneHour) {
            $result = $this->jobHelperDao->notifyLiveStartWithType($live->liveId, 1);
        } else {
            $result = $this->jobHelperDao->notifyLiveStartWithType($live->liveId, 2);
        }
        $this->succeed($result);
    }

    function notifyVideo_get($liveId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $attendances = $this->attendanceDao->getAttendancesByLiveId($liveId, 0, 10000);
        $succeedCount = 0;
        $total = count($attendances);
        foreach ($attendances as $attendance) {
            if ($attendance->videoNotified == 0) {
//                $attends = $this->attendanceDao->getAttendancesByUserId($attendance->userId, 0, 100);
                $ok = $this->weChatPlatform->notifyVideoByWeChat($attendance->userId, $live);
                if ($ok) {
                    $this->attendanceDao->updateToVideoNotified($attendance->userId, $live->liveId);
                    $succeedCount++;
                }
            }
        }
        logInfo('succeedCount:' . $succeedCount . ' total:' . $total);
        $this->succeed(array('succeedCount' => $succeedCount, 'total' => $total));
    }

    function fixAttendanceCount_get()
    {
        $count = $this->liveDao->fixAttendanceCount();
        $this->succeed(array('succeedCount' => $count));
    }

    private function getBjfuUsers()
    {
        $all = file_get_contents(APPPATH . 'data/bjfudata.txt');
        $users = json_decode($all);
        $users = array_slice($users, 800, 0);
        return $users;
    }

    private function getTestUsers()
    {
        $user = new Stdclass();
        $user->username = '李智维';
        $user->mobilePhoneNumber = '18928980893';
        return array(
            $user
        );
    }

    function groupSend_get($liveId)
    {
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $bjfuUsers = $this->getBjfuUsers();
        logInfo("bjfu count:" . count($bjfuUsers));
        $thirdUsers = $bjfuUsers;
//        $thirdUsers = $this->getTestUsers();
        $succeedCount = 0;
        foreach ($thirdUsers as $thirdUser) {
            $ok = $this->sms->groupSend($thirdUser, $live);
            if ($ok) {
                $succeedCount++;
            }
        }
        $total = count($thirdUsers);
        logInfo("succeedCount: " . $succeedCount . ' total:' . $total);;
        $this->succeed(array('succeedCount' => $succeedCount, 'total' => $total));
    }

    function import_get($liveId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $str = file_get_contents(APPPATH . 'data/iDev.json');
        $orders = json_decode($str);
        foreach ($orders as $order) {
            $id = $this->couponDao->addCoupon($order->phone, $liveId);
            if (!$id) {
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
        }
        $this->succeed();
    }

    function error_get($liveId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $ok = $this->liveDao->setLiveError($liveId);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

    function notifyNewLive_get($liveId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $subscribeUsers = $this->userDao->findAllLiveSubscribeUsers();
        $succeedCount = 0;
        foreach ($subscribeUsers as $user) {
            $ok = $this->weChatPlatform->notifyNewLive($user->userId, $live);
            if ($ok) {
                $succeedCount++;
            }
        }
        $this->succeed(
            array(
                'succeedCount' => $succeedCount,
                'total' => count($subscribeUsers)
            )
        );
    }

    function invitationCard_get($liveId)
    {
        if (!function_exists('imagecreate')){
            echo("不支持DG");
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $qrCodeImage = $this->makeQrcode('http://m.quzhiboapp.com/?liveId='.$live->liveId.
                                         '&fromUserId='.$user->userId);
        $cardName = $this->makeInvitationCard($qrCodeImage,
                                           $user->avatarUrl,
                                           $user->username,
                                           $live->subject,
                                           $live->owner->username,
                                           $live->planTs);
        $cardUrl = get_instance()->config->slash_item('base_url').'tmp/'.$cardName;
        if (file_exists("./tmp/".$cardName)){
            $this->succeed(stripslashes($cardUrl));
        }
        else{
            $this->failure("cant not make the invitationCard"."./tmp/".$cardName);
        }

    }

    private function makeInvitationCard($qrCodeImage,$avatarUrl,$username,$subject,$owner,$time)
    {
        $im = imagecreatetruecolor(536, 950);
        //card output path
        $outputName = "card_".rand().".png";
        $outputPath = "./tmp/".$outputName;

        //Resource
        $fontFile = "./resources/fonts/微软vista正黑体.ttf";
        $backgroundUrl = "./resources/images/bg.jpeg";

        //color
        $black = imagecolorallocate($im, 10, 10, 10);
        $blue = imagecolorallocate($im, 0, 0, 252);
        $red = imagecolorallocate($im, 222, 0, 2);

        //bgImage
        $bgImg = imagecreatefromjpeg($backgroundUrl);
        $width = ImageSX($bgImg);//640
        $height = ImageSY($bgImg);//1136

        //username
        $usernameFontSize = 20;
        $usernameWidth = $this->charWidth($usernameFontSize, $fontFile, $username);
        $usernameX = ceil(($width - $usernameWidth) / 2);
        $usernameY = 255;

        //live subject
        $subjectFontSize = 36;
        $subjectWrap = $this->autowrap($subjectFontSize, $fontFile, $subject, 450);
        $subject = $subjectWrap;
        $subjectWidth = $this->charWidth($subjectFontSize, $fontFile, $subject);
        $subjectX = ceil (($width - $subjectWidth) /2);
        $subjectY = 480;

        //owner name
        $ownerFontSize = 20;
        $ownerWidth = $this->charWidth($ownerFontSize, $fontFile, $owner);
        $ownerX = ceil (($width - $ownerWidth) /2);
        $ownerY = 400;

        //time
        $date = date_create($time);
        $time = date_format($date,"Y/m/d H:i");
        $timeFontSize = 25;
        $timeWidth = $this->charWidth($timeFontSize, $fontFile, $time);
        $timeX = ceil (($width - $timeWidth) /2);
        $timeY = 700;

        //avatar
        $avatarImg = $this->openAllTypeImage($avatarUrl);//获得头像图片
        $avatarW = ImageSX($avatarImg);
        $avatarH = ImageSY($avatarImg);
        $avatarSize = 120;
        $avatarX = $width/2 - $avatarSize/2;
        $avatarTop = 95;

        //qrcode
        $qrcodeImg= imagecreatefromstring($qrCodeImage);
        $qrCodeOriginalSize = ImageSX($qrcodeImg);
        $qrCodeSize = 140;
        $qrcodeX = $width/2 - $qrCodeSize/2;
        $qrcodeY = 890;

        //resampled copy avatar
        imagecopyresampled($bgImg, $avatarImg, $avatarX,$avatarTop, 0, 0, $avatarSize, $avatarSize, $avatarW, $avatarH);
        //resampled copy qrcode
        imagecopyresampled($bgImg, $qrcodeImg, $qrcodeX,$qrcodeY, 0, 0, $qrCodeSize, $qrCodeSize, $qrCodeOriginalSize, $qrCodeOriginalSize);
        //user name
        imagettftext($bgImg, $usernameFontSize, 0, $usernameX, $usernameY, $black, $fontFile, $username);//用户名
        //subject
        imagettftext($bgImg, $subjectFontSize, 0, $subjectX, $subjectY, $black, $fontFile, $subject);
        //owner
        imagettftext($bgImg, $ownerFontSize, 0, $ownerX, $ownerY, $black, $fontFile, $owner);
        //plan time
        imagettftext($bgImg, $timeFontSize, 0, $timeX, $timeY, $black, $fontFile, $time);

        //header('content-type:image/gif');  //设置gif Image
        //imagegif($bgImg);

        imagepng($bgImg,$outputPath);
        imagedestroy($bgImg); //销毁
        return $outputName;
    }

    private function openAllTypeImage($image)
    {
        $ename = getimagesize($image);
        $ename = explode('/',$ename['mime']);
        $ext = $ename[1];
        switch($ext){
          case "png":
            $image = imagecreatefrompng($image);
            break;
         case "jpeg":
            $image = imagecreatefromjpeg($image);
            break;
         case "jpg":
            $image = imagecreatefromjpeg($image);
            break;
         case "gif":
            $image = imagecreatefromgif($image);
            break;
        }
        return $image;
    }

    private function makeQrcode($text)
    {
        $qrcode = new QrCode();
        $qrcode
            ->setText($text)
            ->setSize(300)
            ->setMargin(10)
            ->setErrorCorrectionLevel('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setWriterByName('png');
        //header('Content-Type: ' . $qrcode->getContentType());
        //$qrcode->render();
        //echo $qrcode->render();
        return $qrcode->writeString();
    }

   private function charWidth($fontsize, $ttfpath, $char){
      $box = imagettfbbox($fontsize, 0, $ttfpath, $char);
      $width = abs(max($box[2], $box[4]) - min($box[0], $box[6]));
      return $width;
    }

   private function autowrap($fontsize, $fontface, $string, $width) {
      $content = "";
      // 将字符串拆分成一个个单字 保存到数组 letter 中
      for ($i = 0;$i < mb_strlen($string); $i++) {
          $letter[] = mb_substr($string, $i, 1);
      }
      foreach ($letter as $l) {
          $teststr = $content." ".$l;
          $testbox = imagettfbbox($fontsize, 0, $fontface, $teststr);
          // 判断拼接后的字符串是否超过预设的宽度
          if (($testbox[2] > $width) && ($content !== "")) {
              $content .= "\n";
          }
          $content .= $l;
      }
      return $content;
    }
}
