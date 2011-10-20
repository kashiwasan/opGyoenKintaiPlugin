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
    $y = $this->getRequestParameter('year');
    $Y = empty($y)? date("Y") : $y; 
    $m = $this->getRequestParameter('month');
    $M = empty($m)? date("m") : $m;
    $wid = self::getRowId();
    //throw query
    $q = new Zend_Gdata_Spreadsheets_ListQuery();
    $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
    $q->setWorksheetId($wid);
    $query = "id={$member_id} and year={$Y} and month={$M}";
    $q->setSpreadsheetQuery($query);
    $line = $service->getListFeed($q);

    if($line){
      $this->line = $line;
      $this->currentMember = $this->getUser()->getMember()->getId();
      $this->viewmember = $member_id;
      $this->year = $Y;
      $this->month = $M;
      return sfView::SUCCESS;
    }else{
      return sfView::ERROR;
    }
  }

  public function executeRegist(sfWebRequest $request)
  {
    $this->nickname = $this->getUser()->getMember()->getName();
  }

  public function executeSend2(sfWebRequest $request)
  {
    $service = self::getZendGdata();
    $wid = self::getRowId();
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
      $rest = $request->getParameter('rest');
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
      $jitsumu = $end["time"] - $start["time"] - $rest;
      $message = null;

      //Validation
      if(!strlen($data)==9){
         $message.= "入力が不正です<br />";
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
      if($keitai!="S" && $keitai!="Z"){
         $message.= "勤務種別の入力が誤っています。<br />";
      }

      if(!$comment){
         $message.= 'コメントがありません。<br />';
      }

      $unixtime = mktime(0, 0, 0, $m, $d, $y);
      $nowtime = time();
      $pasttime = $unixtime - $nowtime;
      if($pasttime>259200){
         $message.= "勤怠の登録期限がすでに過ぎてしまっています。";
      }

      $q = new Zend_Gdata_Spreadsheets_ListQuery();
      $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
      $q->setWorksheetId($wid);
      $query = "id={$memberId} and year={$y} and month={$m} and date={$d}";
      $q->setSpreadsheetQuery($query);
      $line = $service->getListFeed($q);

      if($line->entries["0"]){
        $message.= '今日の勤怠はすでに登録済みです。<br />';
      }

      if($message)
      {
        $arr = array('status' => 'err', 'msg' => $message);
      }else{
        $start["r"] = $start["hour"].":".$start["minute"];
        $end["r"] = $end["hour"].":".$end["minute"];
        $r = array();
        $j = array();
        $r["hour"] = floor($rest / 60);
        $r["minute"] = $rest - ( $r["hour"] * 60 );
        $r["r"] = $r["hour"].":".$r["minute"];
        $j["hour"] = floor($jitsumu / 60);
        $j["minute"] = $jitsumu - ( $j["hour"] * 60 );
        $j["r"] = $j["hour"].":" .$j["minute"];
        $ymdhis = date("Y/m/d H:i:s");

        $rowData = array(
          'id' => $memberId,
          'year' => $y,
          'month' => $m,
          'date' => $d,
          'rest' => $rest,
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
    $wid = self::getRowId();
    $q = new Zend_Gdata_Spreadsheets_ListQuery();
    $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
    $q->setWorksheetId($wid);
    // $q->setSingleEvents(true);
    $query = "id={$memberId} and year={$y} and month={$m} and date={$d}";
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
            case "rest":
              $rest = $line->getText();
            case "comment":
              $comment = $line->getText();
          }
        }
          $this->nickname = $member_name;
          $this->y = $y;
          $this->m = $m;
          $this->d = $d;
          $this->data = $data;
          $this->rest = $rest;
          $this->comment = $comment;
      }

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
    $wid = self::getRowId();
    if($request->isMethod(sfWebRequest::POST)){
      $y = $request->getParameter('y');
      $m = $request->getParameter('m');
      $d = $request->getParameter('d');
      $data = $request->getParameter('data');
      $rest = $request->getParameter('rest');
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
      $jitsumu = $end["time"] - $start["time"] - $rest;
      $message = null;

      //Validation
      if(!strlen($data)==9){
         $message.= "入力が不正です<br />";
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
      if($keitai!="S" && $keitai!="Z"){
         $message.= "勤務種別の入力が誤っています。<br />";
      }

      if(!$comment){
         $message.= 'コメントがありません。<br />';
      }
      
     $q = new Zend_Gdata_Spreadsheets_ListQuery();
     $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
     $q->setWorksheetId($wid);
     $query = "id={$memberId} and year={$y} and month={$m} and date={$d}";
     $q->setSpreadsheetQuery($query);
     $line = $service->getListFeed($q);

     if(!$line->entries["0"]){
       $message.= '編集しようとした勤怠は存在しませんでした。';
     }else{
       $lineList = $line->entries["0"]->getCustom();
       foreach($lineList as $rows){
         $key = $rows->getColumnName();
         switch($key){
           case "year":
             $y = $rows->getText();
             break;
           case "month":
             $m = $rows->getText();
             break;
           case "date":
             $d = $rows->getText();
             break;
         }
         $nowtime = time();
         $unixtime = mktime(0, 0, 0, $m, $d, $y);
         $pasttime = $nowtime - $unixtime;
         if($pasttime > 259200){   // 259200 = 3 * 24 * 60 * 60
           $message.= "この勤怠はすでに編集不可となっています。";
         }
       }
     }

     if($message)
      {
        $arr = array('status' => 'err', 'msg' => $message);
      }else{
        $start["r"] = $start["hour"].":".$start["minute"];
        $end["r"] = $end["hour"].":".$end["minute"];
        $r = array();
        $j = array();
        $r["hour"] = floor($rest / 60);
        $r["minute"] = $rest - ( $r["hour"] * 60 );
        $r["r"] = $r["hour"].":".$r["minute"];
        $j["hour"] = floor($jitsumu / 60);
        $j["minute"] = $jitsumu - ( $j["hour"] * 60 );
        $j["r"] = $j["hour"].":" .$j["minute"];
        $ymdhis = date("Y/m/d H:i:s");
        $rowData = array(
          'id' => $memberId,
          'year' => $y,
          'month' => $m,
          'date' => $d,
          'rest' => $rest,
          'data' => $data,
          'comment' => $comment,
        );
        $arr = array();
        $spdata = $service->updateRow($line->entries['0'], $rowData);
        if($spdata){
          $arr = array('status' => 'ok', 'msg' => '勤怠を編集しました。');
        }else{
          $arr = array('status' => 'err2', 'msg' => '通信エラーです。（スプレッドシートサーバーと通信ができませんでした。）');
        }
      }
      return $this->renderText(json_encode($arr));
    }else{
      return $this->renderText("Error: POSTリクエストで送信されなかった為、処理を中断しました。");
    }
  }

  public function executeDownloadCSV(){
    //definition
    $service = self::getZendGdata();
    $Id = $this->getRequestParameter('id');
    $member_id = $this->getUser()->getMemberId();
    $MemberS = Doctrine::getTable('Member')->find($member_id);
    $this->member_name = $MemberS->getName();
    $y = $this->getRequestParameter('year');
    $Y = empty($y)? date("Y") : $y;
    $m = $this->getRequestParameter('month');
    $M = empty($m)? date("m") : $m;
    $wid = self::getRowId();
    //throw query
    $q = new Zend_Gdata_Spreadsheets_ListQuery();
    $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
    $q->setWorksheetId($wid);
    $query = "id={$member_id} and year={$Y} and month={$M}";
    $q->setSpreadsheetQuery($query);
    $line = $service->getListFeed($q);

    if($line){
      $this->year = $Y;
      $this->month = $M;
      return sfView::SUCCESS;
    }else{
      return sfView::ERROR;
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

  private function getMemberWorkSheetId($memberId){
    $service = self::getZendGdata();
    $member = Doctrine::getTable('Member')->find($memberId);
    $memberEmailAddress = $member->getEmailAddress(false);
    $memberEmailAddressUserName  = explode("@", $memberEmailAddress);
    $worksheetname = $memberEmailAddressUserName[0];
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

  private function getRowId(){
    $service = self::getZendGdata();
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
