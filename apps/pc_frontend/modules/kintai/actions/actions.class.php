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
    $this->member_name = $MemberS->getName();

    $m = $this->getRequestParameter('month');
    $d = isset($m)? $m : date("m");

    //throw query
    $q = new Zend_Gdata_Spreadsheets_ListQuery();
    $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
    $q->setWorksheetId(opConfig::get('op_kintai_spwid', null));
    // $q->setSingleEvents(true);
    $query = "id={$member_id} and month={$d}";
    $q->setSpreadsheetQuery($query);
    $q->setOrderBy('created');
    $line = $service->getListFeed($q);

    if($line){
      $this->line = $line;
      $this->currentMember = $this->getUser()->getmemberId();
      $this->viewmember = $MemberS->getId();
      return sfView::SUCCESS;
    }else{
      return sfView::ERROR;
    }
  }

  public function executeRegist(sfWebRequest $request)
  {
    $this->nickname = $this->getUser()->getMember()->getName();
  }

  public function executeConfirm(sfWebRequest $request)
  {
    $service = self::getZendGdata();
    if($request->isMethod(sfWebRequest::POST)){
      //Definition
      $memberId = $this->getUser()->getMemberId();
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

      //Validation
      if(!$strlen($data)==9){
         $this->message.= "入力が不正です";
      }
      if(!preg_match("/^[0-2][0-9]$/", $start["hour"]) || !preg_match("/^[0-5][0-9]$/", $start["minute"])){
         $this->message.= "始業時間の入力が誤っています。";
      }
      if(!preg_match("/^[0-2][0-9]$/", $end["hour"]) || !preg_match("/^[0-5][0-9]$", $end["minute"])){
         $this->message.= "終業時間の入力が誤っています。";
      }
      if($jitsumu<=0){
         $this->message.= "実務時間が0分となってしまいます。入力を見なおしてください。";
      }
      if(!$keitai=="S" && !$keitai=="Z"){
         $this->message.= "勤務種別の入力が誤っています。";
      }
      if(!$comment){
         $this->message.= 'コメントがありません。';
      }


      if($this->message)
      {
        return sfView::ERROR;
      }else{
        return sfView::SUCCESS;
      }

    }else{
      $this->redirect('kintai/regist');
      exit;
    }
  }

  public function executeSend(sfWebRequest $request)
  {
    $service = self::getZendGdata();
    if($request->isMethod(sfWebRequest::POST)){
      //Definition
      $memberId = $this->getUser()->getMemberId();
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
      $end["time"] = $end["time"] * 60 + $end["minute"];
      $jitsumu = $end["time"] - $start["time"] - $rest;
      $message = null;

      //Validation
      if(!strlen($data)==9){
         $message.= "入力が不正です<br />";
      }
      if(!preg_match("/^[0-2][0-9]$/", $start["hour"]) || !preg_match("/^[0-5][0-9]$/", $start["minute"])){
         $message.= "始業時間の入力が誤っています。<br />";
      }
      if(!preg_match("/^[0-2][0-9]$/", $end["hour"]) || !preg_match("/^[0-5][0-9]$", $end["minute"])){
         $message.= "終業時間の入力が誤っています。<br />";
      }
      if($jitsumu<=0){
         $message.= "実務時間が0分となってしまいます。入力を見なおしてください。<br />";
      }
      if(!$keitai=="S" && !$keitai=="Z"){
         $message.= "勤務種別の入力が誤っています。<br />";
      }

      if(!$comment){
         $message.= 'コメントがありません。<br />';
      }


      if($message)
      {
        $arr = array('status' => 'err', 'msg' => $message);
      }else{
        $y = date("Y",now());
        $m = date("m", now());
        $d = date("d", now());
        $start["r"] = $start["hour"].":".$start["minute"];
        $end["r"] = $end["hour"].":".$end["minute"];
        $r = array();
        $j = array();
        $r["hour"] = floor($rest / 60);
        $r["minute"] = $rest - ( $r["hour"] * 60 );
        $r["r"] = $r["hour"].":".$r["minute"];
        $j["hour"] = floor($jitsumu / 60);
        $j["minute"] = $jitsumu-($j*60);
        $j["r"] = $j["hour"].":" .$j["minute"];
        $ymdhis = date("Y/m/d H:i:s");
        $rowData = array(
          'member_id' => $memberId,
          'year' => $y,
          'month' => $m,
          'date' => $d,
          'keitai' => $keitai,
          'start' => $start["r"],
          'end' => $end["r"],
          'rest' => $rest["r"],
          'jitsumu' => $j["r"],
          'comment' => $comment,
          'created_at' => $ymdhis,
          'updated_at' => $ymdhis, );
        $arr = array();
        $spdata = $service->insertRow($rowData, opConfig::get('op_kintai_spkey', null), opConfig::get('op_kintai_spwid', null));
        if($spdata){
          $arr = array('status' => 'ok', 'msg' => '勤怠を保存しました。お疲れ様です。');
        }else{
          $arr = array('status' => 'err', 'msg' => '通信エラーです。（スプレッドシートサーバーと通信ができませんでした。）');
        }
      }
      return $this->renderText(json_encode($arr));
    }else{
      $this->redirect('kintai/regist');
      exit;
    }

  }


  public function executeSend2(sfWebRequest $request)
  {
    $service = self::getZendGdata();
    if($request->isMethod(sfWebRequest::POST)){
      //Definition
      $memberId = $this->getUser()->getMemberId();
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

     $q = new Zend_Gdata_Spreadsheets_ListQuery();
     $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
     $q->setWorksheetId(opConfig::get('op_kintai_spwid', null));
     $query = "id={$memberId} and year={$y} month={$m} date={$d}";
     $q->setSpreadsheetQuery($query);
     $line = $service->getListFeed($q);

     if($line){
       $message = '今日の勤怠はすでに登録済みです。';
     }

      if($message)
      {
        $arr = array('status' => 'err', 'msg' => $message);
      }else{
        $y = date("Y");
        $m = date("m");
        $d = date("d");
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
          'keitai' => $keitai,
          'start' => $start["r"],
          'end' => $end["r"],
          'rest' => $r["r"],
          'jitsumu' => $j["r"],
          'comment' => $comment,
          'created' => $ymdhis,
          'updated' => $ymdhis,
          );
        $arr = array();
        $spdata = $service->insertRow($rowData, opConfig::get('op_kintai_spkey', null), opConfig::get('op_kintai_spwid', null));
        if($spdata){
          $arr = array('status' => 'ok', 'msg' => '勤怠を保存しました。お疲れ様です。');
        }else{
          $arr = array('status' => 'err2', 'msg' => '通信エラーです。（スプレッドシートサーバーと通信ができませんでした。）');
        }
      }
      return $this->renderText(json_encode($arr));
    }else{
      $this->redirect('kintai/regist');
      exit;
    }
  }

  public function executeAjaxEdit(sfWebRequest $request){
    $memberId = $this->getUser()->getMemberId();
    $member_name = $this->getUser()->getMember()->getName();
    $this->y = $request->getParameter('y');
    $this->m = $request->getParameter('m');
    $this->d = $request->getParameter('d');
    $service = self::getZendGdata();
    $q = new Zend_Gdata_Spreadsheets_ListQuery();
    $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
    $q->setWorksheetId(opConfig::get('op_kintai_spwid', null));
    // $q->setSingleEvents(true);
    $y = $this->y;
    $m = $this->m;
    $d = $this->d;
    $query = "id={$memberId} and year={$y} and month={$m} and date={$d}";
    $q->setSpreadsheetQuery($query);

    $listFeed = $service->getListFeed($q);
    if(!$listFeed){
      return $renderText("この日の勤怠は存在しないか、既に編集不可能です。");
    }else{
      $rest = array();
      $html[]= "<script type=\"text/javascript\">";
      $html[]= "$(function(){";
      $html[]= "  $(\"#kintai_loading\").hide();";
      $html[]= "  $(\"#kintai_submit\").click(function(){";
      $html[]= "    $(\"#msg\").hide();";
      $html[]= "    $(\"#kintai_loading\").show();";
      $html[]= "    $(\"#kintai_table\").hide();";
      $html[]= "    Data = $(\"#kintai_data\").val();";
      $html[]= "    Rest = $(\"#kintai_rest\").val();";
      $html[]= "    Comment = $(\"#kintai_comment\").val();";
      $html[]= "    $.ajax({";
      $html[]= "      type: \"POST\",";
      $html[]= "      url: \"./kintai/ajaxSend\",";
      $html[]= "      dataType: \"json\",";
      $html[]= "      data: { \"data\":Data, \"rest\":Rest, \"comment\":Comment, \"y\":{$y}, \"m\":{$m}, \"d\":{$d} },";
      $html[]= "      success: function(json){";
      $html[]= "        if(json.status=\"ok\"){";
      $html[]= "          $(\"#msg\").fadeIn(1000).css(\"color\", \"#00FF00\").html(json.msg);";
      $html[]= "        }";
      $html[]= "        if(json.status=\"err\"){";
      $html[]= "          $(\"#msg\").fadeIn(1000).css(\"color\", \"#FF0000\").html(json.msg);";
      $html[]= "        }";
      $html[]= "        $(\"#kintai_loading\").hide();";
      $html[]= "        $(\"#kintai_table\").show();";
      $html[]= "      },";
      $html[]= "    });";
      $html[]= "  return false;";
      $html[]= "  });";
      $html[]= "});";
      $html[]= "</script>";
      $html[]= "";
      $html[]= "<div class=\"partsHeading\"><h3>{$member_name}さんの{$y}年{$m}月{$d}日の勤怠を編集する</h3></div>";
      $html[]= "<div class=\"block\">";
      $html[]= "<div id=\"msg\"></div>";
      $html[]= "<div id=\"kintai_loading\"><img src=\"./opGyoenKintaiPlugin/css/images/prettyPopin/loader.gif\" alt=\"Now Loading...\"/></div>";
      $html[]= "<table id=\"kintai_table\">";

      foreach($listFeed->entries as $entry){
        $line_list = $entry->getCustom();
        foreach($line_list as $line){
          $key = $line->getColumnName();
          switch($key){
            case "keitai":
              $keitai = $line->getText();
            case "start":
              $start = str_replace(":", "", $line->getText());
              $start = mb_substr($start, "0", "4");
            case "end":
              $end = str_replace(":", "", $line->getText());
              $end = mb_substr($end, "0", "4");
            case "rest":
              $rest["data"] = explode(":", $line->getText());
              $rest["time"] = $rest["data"][0] * 60 + $rest["data"][1];
            case "comment":
              $comment = $line->getText();
          }
        }

          $data = $keitai.$start.$end;
          $html[]= "<tr><td>勤怠入力</td><td><input type=\"text\" name=\"kintai_data\" value=\"{$data}\" maxlength=\"9\" id=\"kintai_data\" /></td></tr>";
          $html[]= "<tr><td>休憩時間(分単位)</td><td><input type=\"text\" name=\"kintai_rest\" value=\"{$rest["time"]}\" maxlength=\"3\" id=\"kintai_rest\" /></td></tr>";
          $html[]= "<tr><td>作業内容コメント</td><td><textarea name=\"kintai_comment\" id=\"kintai_comment\">{$comment}</textarea></td></tr>";

      }
      $html[]= '<tr><td></td><td><input type="submit" name="submit" id="kintai_submit" value="修正する" /></td></tr>';
      $html[]= '</table>';
      $html[]= '</div>';
      $htmls = implode("\n", $html);
      return $this->renderText($htmls);
    }



  }

  public function executeAjaxSend(sfWebRequest $request){
    $service = self::getZendGdata();
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
     $q->setWorksheetId(opConfig::get('op_kintai_spwid', null));
     $query = "id={$memberId} and year={$y} and month={$m} and date={$d}";
     $q->setSpreadsheetQuery($query);
     $line = $service->getListFeed($q);

     if(!$line){
       $message = '編集しようとした勤怠は存在しませんでした。';
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
          'keitai' => $keitai,
          'start' => $start["r"],
          'end' => $end["r"],
          'rest' => $r["r"],
          'jitsumu' => $j["r"],
          'comment' => $comment,
          'created' => $ymdhis,
          'updated' => $ymdhis,
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

  private function generate_hash($memberId)
  {
    $d = time() - 10800;
//    $date = date('Y-m-d', $d)
//  $hash = $date.'-'.$memberId;
    return sha1($d);
  }

  private function getZendGdata()
  {
  //    require_once('Zend/Loader.php');
  //    Zend_Loader::loadClass('Zend_Gdata');
  //    Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
  //    Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');

    $id = Doctrine::getTable('SnsConfig')->get('op_kintai_spid');
    $pw = Doctrine::getTable('SnsConfig')->get('op_kintai_sppw');
    $service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
    $client = Zend_Gdata_ClientLogin::getHttpClient($id, $pw, $service);
    return new Zend_Gdata_Spreadsheets($client);
  }
  
}
