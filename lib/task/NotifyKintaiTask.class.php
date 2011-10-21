<?php
class NotifyKintaiTask extends sfBaseTask
{
  protected function configure() {
    mb_language('Japanese');
    mb_internal_encoding('utf-8');

    $this->namespace = 'opKintai';
    $this->name      = 'notify';
    $this->aliases   = array('kintai-notify');
    $this->briefDescription = 'Notify to the member who do not post the works on Activity.';
    $this->addArgument('mode', null , sfCommandOption::PARAMETER_REQUIRED, 'mode');
  }

  protected function execute($arguments = array(), $options = array()) {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $url = sfConfig::get('op_base_url');
    if($arguments['mode']=='morning'){
      $message = 'おはようございます。昨日の勤怠報告が済んでいない方は報告よろしくお願いします。';
    }elseif($arguments['mode']=='afternoon'){
      $message = 'お疲れ様です。退勤される方は勤怠報告をよろしくおねがいします。';
    }elseif($arguments['mode']=='evening'){
      $message = 'お疲れ様です。退勤される方は勤怠報告をよろしくおねがいします。';
    }else{
      $message = '';
    }
    if($message){
      $message = $message.' '.$url.'/kintai';
      $activity = new ActivityData();
      $activity->setMemberId(1);
      $activity->setBody($message);
      $activity->setIsMobile(0);
      $activity->save();
      echo "Posted via Acvitity.\n";
    }else{
      echo "Posted failure. Maybe incorrect arguments.\n";
    }
  }
}
