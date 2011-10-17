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
    //testtest
    $service = self::getZendGdata();
    $Id = $this->getRequestParameter('id');
    $member_id = isset($Id) ? $Id : $this->getUser()->getMemberId();
    $MemberS = Doctrine::getTable('Member')->find($member_id);
    if(!$MemberS){ return sfView::ERROR; }
    $this->member_name = $MemberS->getName();
    $y = $this->getRequestParameter('year');
    $Y = is_null($y)? date("Y") : $y; 
    $m = $this->getRequestParameter('month');
    $M = is_null($m)? date("m") : $m;

    //throw query
    $q = new Zend_Gdata_Spreadsheets_ListQuery();
    $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
    $q->setWorksheetId(opConfig::get('op_kintai_spwid', null));
    // $q->setSingleEvents(true);
    $query = "id={$member_id} and year={$Y} and month={$M}";
    $q->setSpreadsheetQuery($query);
    $q->setOrderBy('created');
    $line = $service->getListFeed($q);

    if($line){
      $this->line = $line;
      $this->currentMember = $this->getUser()->getmemberId();
      $this->viewmember = $MemberS->getId();
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
      $q->setWorksheetId(opConfig::get('op_kintai_spwid', null));
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
          // 'keitai' => $keitai,
          // 'start' => $start["r"],
          // 'end' => $end["r"],
          'rest' => $rest,
          // 'jitsumu' => $j["r"],
          'data' => $data,
          'comment' => $comment,
          // 'created' => $ymdhis,
          // 'updated' => $ymdhis,
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

  public function executeAjaxRegist(sfWebRequest $request){
    $nickname = $this->getUser()->getMember()->getName();
    $y = $request->getParameter('y');
      if(empty($y)){ $y = date('Y'); }
    $m = $request->getParameter('m');
      if(empty($m)){ $m = date('m'); }
    $d = $request->getParameter('d');
      if(empty($d)){ $d = date('d'); }
 
    $html[]= "<script type=\"text/javascript\">";
    $html[]= "$(function(){";
    $html[]= "  $(\"#kintai_regist_loading\").hide();";
    $html[]= "    $(\"#kintai_regist_submit\").click(function(){";
    $html[]= "      $(\"#kintai_regist_loading\").show();";
    $html[]= "      $(\"#regist_msg\").hide();";
    $html[]= "      Data = $(\"#kintai_regist_data\").val();";
    $html[]= "      Rest = $(\"#kintai_regist_rest\").val();";
    $html[]= "      Comment = $(\"#kintai_regist_comment\").val();";
    $html[]= "      $.ajax({";
    $html[]= "        type: \"POST\",";
    $html[]= "        url: \"./kintai/send2\",";
    $html[]= "        dataType: \"json\",";
    $html[]= "        data: { \"data\":Data, \"rest\":Rest, \"comment\":Comment, \"y\": {$y}, \"m\": {$m}, \"d\": {$d}},";
    $html[]= "        success: function(json){";
    $html[]= "          $(\"#kintai_regist_loading\").hide();";
    $html[]= "          if(json.status=\"ok\"){";
    $html[]= "            $(\"#regist_msg\").fadeIn(\"1000\").css(\"color\", \"#00FF00\").html(json.msg);";    
    $html[]= "          }";
    $html[]= "          if(json.status=\"err\"){";
    $html[]= "            $(\"#regist_msg\").fadeIn(\"1000\").css(\"color\", \"#FF0000\").html(json.msg);";    
    $html[]= "          }";
    $html[]= "        }";    
    $html[]= "    });";
    $html[]= "    return false;";    
    $html[]= "  });";
    $html[]= "});";
    $html[]= "</script>";
    $html[]= "<div class=\"partsHeading\"><h3>{$nickname}さんの今日の勤怠を登録する</h3></div>";
    $html[]= "<div class=\"block\">";
    $html[]= "<div id=\"kintai_regist_loading\" style=\"display: none;\"><img src=\"./opGyoenKintaiPlugin/js/loading.gif\" alt=\"Now Loading...\" /></div>";
    $html[]= "<div id=\"regist_msg\"></div>";
    $html[]= "<table>";
    $html[]= "<tr><td>今日の日付</td><td>".date("Y/m/d H:i:s")."</td></tr>";
    $html[]= "<tr>";
    $html[]= "<td><label for=\"data\">勤怠入力</label></td><td><input type=\"text\" id=\"kintai_regist_data\" name=\"data\" value=\"\" maxlength=\"9\" /></td></tr>";
    $html[]= "<tr>";
    $html[]= "<td><label for=\"rest\">休憩時間(分単位)</label></td><td><input type=\"text\" id=\"kintai_regist_rest\" name=\"rest\" value=\"\" maxlength=\"3\"></td></tr>";
    $html[]= "<tr>";
    $html[]= "<td><label for=\"comment\">作業内容コメント</label></td><td><textarea name=\"comment\" id=\"kintai_regist_comment\"></textarea></td></tr>";
    $html[]= "<tr><td></td><td><input type=\"submit\" name=\"submit\" id=\"kintai_regist_submit\" value=\"確認する\" /></td></tr></table>";
    $html[]= "</div>";
    $htmls = implode("\n", $html );
    return $this->rendertext($htmls);
  }

  public function executeAjaxEdit(sfWebRequest $request){
    $memberId = $this->getUser()->getMemberId();
    $member_name = $this->getUser()->getMember()->getName();
    $y = $request->getParameter('y');
    $m = $request->getParameter('m');
    $d = $request->getParameter('d');
    $service = self::getZendGdata();
    $q = new Zend_Gdata_Spreadsheets_ListQuery();
    $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
    $q->setWorksheetId(opConfig::get('op_kintai_spwid', null));
    // $q->setSingleEvents(true);
    $query = "id={$memberId} and year={$y} and month={$m} and date={$d}";
    $q->setSpreadsheetQuery($query);

    $listFeed = $service->getListFeed($q);
    if(!$listFeed->entries["0"]){
      return $this->renderText("この日の勤怠は存在しないか、既に編集不可能です。");
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
          /****************************
            case "keitai":
              $keitai = $line->getText();
            case "start":
              $start = str_replace(":", "", $line->getText());
              if(strlen($start)==5){
                $start = "0".$start;
              }
              $start = mb_substr($start, "0", "4");
            case "end":
              $end = str_replace(":", "", $line->getText());
              if(strlen($end)==5){
                $end = "0".$end;
                $end = mb_substr($end, "0", "4");
              }else{
                $end = mb_substr($end, "0", "4");
              }
          ******************************/
            case "data":
              $data = $line->getText();
            case "rest":
              $rest = $line->getText();
            case "comment":
              $comment = $line->getText();
          }
        }

          $html[]= "<tr><td>勤怠入力</td><td><input type=\"text\" name=\"kintai_data\" value=\"{$data}\" maxlength=\"9\" id=\"kintai_data\" /></td></tr>";
          $html[]= "<tr><td>休憩時間(分単位)</td><td><input type=\"text\" name=\"kintai_rest\" value=\"{$rest}\" maxlength=\"3\" id=\"kintai_rest\" /></td></tr>";
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

     if(!$line->entries["0"]){
       $message.= '編集しようとした勤怠は存在しませんでした。';
     }else{
       $lineList = $line->entries["0"]->getCustom();
       foreach($lineList as $rows){
         $key = $rows->getColumnname();
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
          //'keitai' => $keitai,
          //'start' => $start["r"],
          //'end' => $end["r"],
          'rest' => $rest,
          //'jitsumu' => $j["r"],
          'data' => $data,
          'comment' => $comment,
          //'created' => $ymdhis,
          //'updated' => $ymdhis,
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
