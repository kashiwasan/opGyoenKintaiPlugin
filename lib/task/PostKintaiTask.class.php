<?php
class PostKintaiTask extends sfBaseTask
{
  public function configure()
  {
    mb_language('Japanese');
    mb_internal_encoding('utf-8');
    $this->namespace = 'opKintai';
    $this->name      = 'execute';
    $this->aliases = array('kintai-bot');
    $this->breafDescription  = 'execute opGyoenKintaiPlugin bot';
  }

  public function execute($arguments = array(), $options = array()){
    echo "START KINTAI BOT.";
    $detail = array();
    $databaseManager = 	new sfDatabaseManager($this->configuration);
    $y = date('Y');
    $m = date('m');
    $d = date('d');
    $service = opGyoenKintaiPluginUtil::getZendGdata(opConfig::get('op_kintai_spid'), opConfig::get('op_kintai_sppw'));
    $members = Doctrine::getTable('Member')->findAll();
    foreach($members as $member){
      $memberId = $member->getId();
      $memberConfig = Doctrine::getTable('MemberConfig')->findByMemberIdAndName($memberId, 'op_kintai_member_wid');
      $config = $memberConfig->getValue();
      $q = new Zend_Gdata_Spreadsheets_ListQuery();
      $q->setSpreadsheetkey(opConig::get('op_kintai_spkey'));
      $q->setWorkSheetId(opConfig::get('op_kintai_spwid'));
      $query = "id={$memberId} and y={$y} and m={$m}";
      $q->setSpreadsheetQuery($query);
      $line_list = $service->getListFeed($q);
      foreach($line_list->entries as $entry){
        $line = $entry->getCustom();
        foreach($line as $list){
          $key = $line->getColumnName();
          switch($key){
            case "year":
              $y = $list->getValue();
              break;
            case "month": 
              $m = $list->getValue();
              break;
            case "date":
              $d = $list->getValue();
              break;
            case "data":
              $d = $list->getValue();
              break;
            case "rest":
              $rest = $list->getValue();
              break;
            case "comment":
              $comment = $list->getValue();
              break;
          }
          $keitai = substr($data, 0, 1);
          // if($keitai=="S"){ $keitai = "出社"; }else{ $keitai = "在宅"; }
          $sh = substr($data, 1, 2);
          $sm = substr($data, 3, 2);
          $eh = substr($data, 5, 2);
          $em = substr($data, 7, 2);
          $rh = floor($rest / 60);
          $rm = $rest - ( $rh * 60 ) 
          $detail[$d] = array('year' => $y, 'month' => $m, 'date' => $d, 'keitai' => $keitai, 'sh' => $sh, 'sm' => $sm, 'eh' => $eh, 'em' => $em, 'rh' => $rh, 'rm' => $rm);
        }
      }
    }
  }
}


