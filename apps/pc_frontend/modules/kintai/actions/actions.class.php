<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * kintai actions.
 *
 * @package    OpenPNE
 * @subpackage opGyoenKintaiPlugin
 * @author     Shouta Kashiwagi <kashwagi@tejimaya.com> 
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class kintaiActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    //definition
    $service = self::getZendGdata();
    $Id = $this->getRequestParameter('id');
    $member_id = isset($Id) ? $Id : $this->getUser()->getMemberId();
    $MemberS = Doctrine::getTable('Member')->find($member_id);
    if(!$MemberS){ return sfView::ERROR; }
    if ($member_id !== $this->getUser()->getMemberId()){
      sfConfig::set('sf_nav_type', 'friend');
      sfConfig::set('sf_nav_id', $member_id);
    }
    $this->member_name = $MemberS->getName();
    $wid = self::getRowId($service);

    for($i=0;$i<3;$i++){
      if($i==0){
        $date = date('Y/m/d', strtotime('now'));
      }elseif($i==1){
        $date = date('Y/m/d', strtotime('yesterday'));
      }else{
        $date = date('Y/m/d', strtotime("-{$i} days"));
      }
      //throw query
      $q = new Zend_Gdata_Spreadsheets_ListQuery();
      $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
      $q->setWorksheetId($wid);
      $query = "id={$member_id} and date={$date}";
      $q->setSpreadsheetQuery($query);
      $line = $service->getListFeed($q);
      if($line->entries["0"]){
        $data[]= array('date'=> $date, 'flg' => 1,);
      }else{
        $data[]= array('date'=> $date, 'flg' => 0,);
      }
    }
    
    $memberSpreadSheetKey = self::getMemberMasterSpreadSheetKey($service, $member_id);
    $this->member_splink = "https://spreadsheets.google.com/ccc?key=".$memberSpreadSheetKey."&hl=ja";
    $memberEditableKey = self::getMemberSpreadSheetKey($service, $member_id);
    if(!is_null($memberEditableKey)){
      $this->member_editablelink = "https://spreadsheets.google.com/ccc?key=".$memberEditableKey."&hl=ja";
    }else{
      $this->member_editablelink = null;
    }
    $this->data = $data;
    $this->currentMember = $this->getUser()->getMember()->getId();
    $this->viewmember = $member_id;
    //$this->allowdate = opConfig::get('op_kintai_allowdate', '3');
    return sfView::SUCCESS;
  }

  public function executeRegist(sfWebRequest $request)
  {
    $this->nickname = $this->getUser()->getMember()->getName();
  }

  public function executeSend2(sfWebRequest $request)
  {
    $service = self::getZendGdata();
    $wid = self::getRowId($service);
    if($request->isMethod(sfWebRequest::POST)){
      //Definition
      $memberId = $this->getUser()->getMemberId();
      $y = $request->getParameter('y');
      if(empty($y)){ $y = date('Y'); }
      $m = $request->getParameter('m');
      if(empty($m)){ $m = date('m'); }
      $d = $request->getParameter('d');
      if(empty($d)){ $d = date('d'); }
      $data = $request->getParameter('data');
      $comment = $request->getParameter('comment');

      $keitai = substr($data, 0, 1);
      $start = array();
      $end = array();
      $start["hour"]= substr($data, 1, 2);
      $start["minute"] = substr($data, 3, 2);
      $end["hour"] = substr($data, 5, 2);
      $end["minute"] = substr($data, 7, 2);
      $start["time"] = $start["hour"] * 60 + $start["minute"];
      $end["time"] = $end["hour"] * 60 + $end["minute"];
      $rest = substr($data, 9, 3);
      if(substr($rest, 0, 1)=="0"){
        $rest = substr($rest, 1, 2);
      }
      $jitsumu = $end["time"] - $start["time"] - $rest;
      
      if(strlen($data)==24){ 
        $start2["hour"]= substr($data, 12, 2);
        $start2["minute"] = substr($data, 14, 2);
        $end2["hour"] = substr($data, 16, 2);
        $end2["minute"] = substr($data, 18, 2);
        $start2["time"] = $start2["hour"] * 60 + $start2["minute"];
        $end2["time"] = $end2["hour"] * 60 + $end2["minute"];
        $rest2 = substr($data, 20, 3);
        if(substr($rest2, 0, 1)=="0"){
          $rest2 = substr($rest2, 1, 2);
        }
        $jitsumu2 = $end2["time"] - $start2["time"] - $rest2;
      }

      //Validation
      $message = null;
      if(strlen($data)!=12 && strlen($data)!=24){
        $message.= "入力が不正です。<br />";
      }
      if(!preg_match("/^[0-2][0-9]$/", $start["hour"]) || !preg_match("/^[0-5][0-9]$/", $start["minute"])){
        $message.= "始業時間の入力が誤っています。<br />";
      }
      if(!preg_match("/^[0-2][0-9]$/", $end["hour"]) || !preg_match("/^[0-5][0-9]$/", $end["minute"])){
        $message.= "終業時間の入力が誤っています。<br />";
      }
      if($jitsumu<=0){
        $message.= "実務時間が0分となってしまいます。入力を見なおしてください。<br />";
      } 
      if(!preg_match("/^\d{2,3}$/", $rest)){
        $message.= "休憩時間の入力が誤っています。<br />";
      }
      if($keitai!="S" && $keitai!="Z"){
        $message.= "勤務種別の入力が誤っています。<br />";
      }
 
      if(!$comment){
        $message.= 'コメントがありません。<br />';
      }
      if(strlen($data)==24){
        if(isset($keitai) && isset($keitai2) && $keitai==$keitai2){
          $message.= "同じ業務種別です。";
        }
        if(!preg_match("/^[0-2][0-9]$/", $start2["hour"]) || !preg_match("/^[0-5][0-9]$/", $start2["minute"])){
          $message.= "始業時間の入力が誤っています。<br />";
        }
        if(!preg_match("/^[0-2][0-9]$/", $end2["hour"]) || !preg_match("/^[0-5][0-9]$/", $end2["minute"])){
          $message.= "終業時間の入力が誤っています。<br />";
        }
        if($jitsumu<=0){
          $message.= "実務時間が0分となってしまいます。入力を見なおしてください。<br />";
        } 
        if(!preg_match("/^\d{2,3}$/", $rest2)){
          $message.= "休憩時間の入力が誤っています。<br />";
        }  
        if($keitai2!="S" && $keitai2!="Z"){
          $message.= "勤務種別の入力が誤っています。<br />";
        }
      }

      $unixtime = mktime(0, 0, 0, $m, $d, $y);
      $nowtime = time();
      $pasttime = $unixtime - $nowtime;
      $allowtime = opConfig::get('op_kintai_allowdate', '3');
      if($pasttime>$allowtime){
         $message.= "勤怠の登録期限がすでに過ぎてしまっています。<br />";
      }

      if(!$message){
        $q = new Zend_Gdata_Spreadsheets_ListQuery();
        $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
        $q->setWorksheetId($wid);
        $query = "id={$memberId} and date={$y}/{$m}/{$d}";
        $q->setSpreadsheetQuery($query);
        $line = $service->getListFeed($q);

        if($line->entries["0"]){
          $message.= "この種別の勤怠はすでに登録済みです。<br />";
        }
      }

      if($message)
      {
        $arr = array('status' => 'err', 'msg' => $message);
      }else{
        $ymdhis = "{$y}/{$m}/{$d}";
        $rowData = array(
          'id' => $memberId,
          'date' => $ymdhis,
          'data' => $data,
          'comment' => $comment,
          );
        $arr = array();
        $spdata = $service->insertRow($rowData, opConfig::get('op_kintai_spkey', null), $wid);
        if($spdata){
          $arr = array('status' => 'ok', 'msg' => '勤怠を保存しました。お疲れ様です。');
        }else{
          $arr = array('status' => 'err2', 'msg' => '通信エラーです。（スプレッドシートサーバーと通信ができませんでした。）');
        }
      }
      return $this->renderText(json_encode($arr));
    }else{
      $this->redirect('kintai');
      exit;
    }
  }
  public function executeAjaxRegist(sfWebRequest $request){
    $this->nickname = $this->getUser()->getMember()->getName();
    $y = $request->getParameter('y');
      if(empty($y)){ $y = date('Y'); }
    $m = $request->getParameter('m');
      if(empty($m)){ $m = date('m'); }
    $d = $request->getParameter('d');
      if(empty($d)){ $d = date('d'); }

    $this->data = $request->getParameter('keitai').$request->getParameter('sh').$request->getparameter('sm').$request->getparameter('eh').$request->getParameter('em');
    $this->rest = $request->getParameter('rest');
    $this->comment = $request->getParameter('comment');

    $this->y = $y;
    $this->m = $m;
    $this->d = $d;
    $this->setLayout(false);
    return sfView::SUCCESS;
  }


  public function executeAjaxRegistEasy(sfWebRequest $request){
    $this->nickname = $this->getUser()->getMember()->getName();
    $y = $request->getParameter('y');
      if(empty($y)){ $y = date('Y'); }
    $m = $request->getParameter('m');
      if(empty($m)){ $m = date('m'); }
    $d = $request->getParameter('d');
      if(empty($d)){ $d = date('d'); }
    $this->y = $y;
    $this->m = $m;
    $this->d = $d;
    $this->setLayout(false);
    return sfView::SUCCESS;
  }


  public function executeAjaxEdit(sfWebRequest $request){
    $memberId = $this->getUser()->getMemberId();
    $member_name = $this->getUser()->getMember()->getName();
    $y = $request->getParameter('y');
    $m = $request->getParameter('m');
    $d = $request->getParameter('d');
    $service = self::getZendGdata();
    $wid = self::getRowId($service);
    $q = new Zend_Gdata_Spreadsheets_ListQuery();
    $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
    $q->setWorksheetId($wid);
    $query = "id={$memberId} and date={$y}/{$m}/{$d}";
    $q->setSpreadsheetQuery($query);

    $listFeed = $service->getListFeed($q);
    if(!$listFeed->entries["0"]){
      return $this->renderText("この日の勤怠は存在しないか、既に編集不可能です。");
    }else{
      foreach($listFeed->entries as $entry){
        $line_list = $entry->getCustom();
        foreach($line_list as $line){
          $key = $line->getColumnName();
          switch($key){
            case "data":
              $data = $line->getText();
            case "comment":
              $comment = $line->getText();
          }
        }
      }
      $this->nickname = $member_name;
      $this->y = $y;
      $this->m = $m;
      $this->d = $d;
      $this->data = $data;
      $this->rest = $rest;
      $this->comment = $comment;
      $this->setLayout(false);
      return sfView::SUCCESS;
    }
  }

  public function executeAjaxSend(sfWebRequest $request){
    $service = self::getZendGdata();
    $wid = self::getRowId($service);
    if($request->isMethod(sfWebRequest::POST)){
      $y = $request->getParameter('y');
      $m = $request->getParameter('m');
      $d = $request->getParameter('d');
      $data = $request->getParameter('data');
      $comment = $request->getParameter('comment');
      $memberId = $this->getUser()->getMemberId();
      $keitai = substr($data, 0, 1);
      $start = array();
      $end = array();
      $start["hour"]= substr($data, 1, 2);
      $start["minute"] = substr($data, 3, 2);
      $end["hour"] = substr($data, 5, 2);
      $end["minute"] = substr($data, 7, 2);
      $start["time"] = $start["hour"] * 60 + $start["minute"];
      $end["time"] = $end["hour"] * 60 + $end["minute"];
      $rest = substr($data, 9 , 3);
      $jitsumu = $end["time"] - $start["time"] - $rest;

      if(strlen($data)==24){ 
        $start2["hour"]= substr($data, 12, 2);
        $start2["minute"] = substr($data, 14, 2);
        $end2["hour"] = substr($data, 16, 2);
        $end2["minute"] = substr($data, 18, 2);
        $start2["time"] = $start2["hour"] * 60 + $start2["minute"];
        $end2["time"] = $end2["hour"] * 60 + $end2["minute"];
        $rest2 = substr($data, 20, 3);
        if(substr($rest2, 0, 1)=="0"){
          $rest2 = substr($rest2, 1, 2);
        }
        $jitsumu2 = $end2["time"] - $start2["time"] - $rest2;
      }
      
      //Validation
      $message = null;
      if(strlen($data)!=12 && strlen($data)!=24){
        $message.= "入力が不正です。<br />";
      }
      if(!preg_match("/^[0-2][0-9]$/", $start["hour"]) || !preg_match("/^[0-5][0-9]$/", $start["minute"])){
        $message.= "始業時間の入力が誤っています。<br />";
      }
      if(!preg_match("/^[0-2][0-9]$/", $end["hour"]) || !preg_match("/^[0-5][0-9]$/", $end["minute"])){
        $message.= "終業時間の入力が誤っています。<br />";
      }
      if($jitsumu<=0){
        $message.= "実務時間が0分となってしまいます。入力を見なおしてください。<br />";
      } 
      if(!preg_match("/^\d{2,3}$/", $rest)){
        $message.= "休憩時間の入力が誤っています。<br />";
      }
      if($keitai!="S" && $keitai!="Z"){
        $message.= "勤務種別の入力が誤っています。<br />";
      }
 
      if(!$comment){
        $message.= 'コメントがありません。<br />';
      }
      if(strlen($data)==24){
        if(isset($keitai) && isset($keitai2) && $keitai==$keitai2){
          $message.= "同じ業務種別です。<br />";
        }

        if(!preg_match("/^[0-2][0-9]$/", $start2["hour"]) || !preg_match("/^[0-5][0-9]$/", $start2["minute"])){
          $message.= "始業時間の入力が誤っています。<br />";
        }
        if(!preg_match("/^[0-2][0-9]$/", $end2["hour"]) || !preg_match("/^[0-5][0-9]$/", $end2["minute"])){
          $message.= "終業時間の入力が誤っています。<br />";
        }
        if($jitsumu<=0){
          $message.= "実務時間が0分となってしまいます。入力を見なおしてください。<br />";
        }   
        if(!preg_match("/^\d{2,3}$/", $rest2)){
          $message.= "休憩時間の入力が誤っています。<br />";
        }
        if($keitai2!="S" && $keitai2!="Z"){
          $message.= "同じ勤務種別は入力できません。<br />";
        }
      }
      $q = new Zend_Gdata_Spreadsheets_ListQuery();
      $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
      $q->setWorksheetId($wid);
      $query = "id={$memberId} and date={$y}/{$m}/{$d}";
      $q->setSpreadsheetQuery($query);
      $line = $service->getListFeed($q);

      if(!$line->entries["0"]){
        $message.= '編集しようとした勤怠は存在しませんでした。<br />';
      }else{
          $nowtime = time();
          $unixtime = mktime(0, 0, 0, $m, $d, $y);
          $pasttime = $nowtime - $unixtime;
          $allowtime = opConfig::get('op_kintai_allowdate', '3') * 24 * 60 * 60;
          if($pasttime > $allowtime){   // 259200 = 3 * 24 * 60 * 60
            $message.= "この勤怠はすでに編集不可となっています。<br />";
          }
      }

      if($message)
      {
        $arr = array('status' => 'err', 'msg' => $message);
      }else{
        $ymdhis = "{$y}/{$m}/{$d}";
        $rowData = array(
          'id' => $memberId,
          'date' => $ymdhis,
          'data' => $data,
          'comment' => $comment,
        );
        $arr = array();
        $spdata = $service->updateRow($line->entries['0'], $rowData);
        if($spdata){
          $arr = array('status' => 'ok', 'msg' => '勤怠を編集しました。<br />');
        }else{
          $arr = array('status' => 'err2', 'msg' => '通信エラーです。（スプレッドシートサーバーと通信ができませんでした。）');
        }
      }
      return $this->renderText(json_encode($arr));
    }else{
      return $this->renderText("Error: POSTリクエストで送信されなかった為、処理を中断しました。");
    }
  }

  private function getZendGdata()
  {
    $id = Doctrine::getTable('SnsConfig')->get('op_kintai_spid');
    $pw = Doctrine::getTable('SnsConfig')->get('op_kintai_sppw');
    $service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
    $client = Zend_Gdata_ClientLogin::getHttpClient($id, $pw, $service);
    return new Zend_Gdata_Spreadsheets($client);
  } 

  private function getMemberSpreadSheetKey($service, $memberId){
    $member = Doctrine::getTable('Member')->find($memberId);
    $memberEmailAddress = $member->getEmailAddress(false);
    $spreadsheetname = $memberEmailAddress."-kintai";
    $feed = $service->getSpreadsheetFeed();
    $i = 0;
    foreach($feed->entries as $entry) {
      if($entry->title->text===$spreadsheetname) {
        $aKey = split('/', $feed->entries[$i]->id->text);
        $SpreadsheetKey = $aKey[5];
        break;
      }
      $i++;
    }
    if($SpreadsheetKey){
      return $SpreadsheetKey;
    }else{
      return null;
    }
  }

  private function getMemberWorkSheetId($service, $spreadsheetKey){
    $worksheetname = "勤怠明細";
    $DocumentQuery = new Zend_Gdata_Spreadsheets_DocumentQuery();
    $DocumentQuery->setSpreadsheetKey($spreadsheetKey);
    $SpreadsheetFeed = $service->getWorksheetFeed($DocumentQuery);
    $i = 0;
    foreach($SpreadsheetFeed->entries as $WorksheetEntry) {
      $worksheetId = split('/', $SpreadsheetFeed->entries[$i]->id->text);
      if($WorksheetEntry->title->text===$worksheetname){
         $WorksheetId = $worksheetId[8];
         break;
      }
      $i++;
    }
    return $WorksheetId;
  }

  private function getMemberMasterSpreadSheetKey($service, $memberId){
    $member = Doctrine::getTable('Member')->find($memberId);
    $memberEmailAddress = $member->getEmailAddress(false);
    $spreadsheetname = "(Master) ".$memberEmailAddress."-kintai";
    $feed = $service->getSpreadsheetFeed();
    $i = 0;
    foreach($feed->entries as $entry) {
      if( $entry->title->text===$spreadsheetname) {
        $aKey = split('/', $feed->entries[$i]->id->text);
        $SpreadsheetKey = $aKey[5];
        break;
      }
      $i++;
    }
    if($SpreadsheetKey){
      return $SpreadsheetKey;
    }else{
      return null;
    }
  }

  private function getMemberMasterWorkSheetId($service, $spreadsheetKey){
    $worksheetname = "勤怠明細";
    $DocumentQuery = new Zend_Gdata_Spreadsheets_DocumentQuery();
    $DocumentQuery->setSpreadsheetKey($spreadsheetKey);
    $SpreadsheetFeed = $service->getWorksheetFeed($DocumentQuery);
    $i = 0;
    foreach($SpreadsheetFeed->entries as $WorksheetEntry) {
      $worksheetId = split('/', $SpreadsheetFeed->entries[$i]->id->text);
      if($WorksheetEntry->title->text===$worksheetname){
         $WorksheetId = $worksheetId[8];
         break;
      }
      $i++;
    }
    return $WorksheetId;
  }


  private function getRowId($service){
    $worksheetname = "RAW";
    $DocumentQuery = new Zend_Gdata_Spreadsheets_DocumentQuery();
    $DocumentQuery->setSpreadsheetKey(opConfig::get('op_kintai_spkey'));
    $SpreadsheetFeed = $service->getWorksheetFeed($DocumentQuery);
    $i = 0;
    foreach($SpreadsheetFeed->entries as $WorksheetEntry) {
      $worksheetId = split('/', $SpreadsheetFeed->entries[$i]->id->text);
      if($WorksheetEntry->title->text===$worksheetname){
         $WorksheetId = $worksheetId[8];
         break;
      }
      $i++;
    }
    return $WorksheetId;
  }
}
