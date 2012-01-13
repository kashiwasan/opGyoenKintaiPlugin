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
    $memberS = Doctrine::getTable('Member')->find($member_id);
    if (!$memberS)
    {
      return sfView::ERROR;
    }
    if ($member_id !== $this->getUser()->getMemberId())
    {
      sfConfig::set('sf_nav_type', 'friend');
      sfConfig::set('sf_nav_id', $member_id);
    }
    $this->member_name = $memberS->getName();
    $memberSpreadSheetKey = self::getMemberMasterSpreadSheetKey($service, $member_id);
    $domain = null;
    if (!is_null(opConfig::get('op_kintai_apps_domain', null)))
    {
      $domain = 'a/'.opConfig::get('op_kintai_apps_domain').'/';
    }
    $this->member_splink = "https://docs.google.com/".$domain."spreadsheet/ccc?key=".$memberSpreadSheetKey."&hl=ja";
    $memberEditableKey = self::getMemberSpreadSheetKey($service, $member_id);
    if (!is_null($memberEditableKey))
    {
      $this->member_editablelink = "https://docs.google.com/".$domain."spreadsheet/ccc?key=".$memberEditableKey."&hl=ja";
    }
    else
    {
      $this->member_editablelink = null;
    }
    $this->data = $data;
    $this->currentMember = $this->getUser()->getMemberId();
    $this->viewMember = $member_id;
    return sfView::SUCCESS;
  }

  public function executeAjaxList(sfWebRequest $request)
  {
    $service = self::getZendGdata();
    $wid = self::getRowId($service);
    $id = $request->getParameter('id');
    $member_id = isset($id) ? $id : $this->getUser()->getMemberId();
    for ($i=0;$i<3;$i++)
    {
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
      list($y, $m, $d) = split('/', $date);
      if ($line->entries["0"])
      {
        foreach ($line as $list)
        {
          $cols = $list->getCustom();
          foreach ($cols as $col)
          {
            $key = $col->getColumnName();
            switch ($key)
            {
              case "data" :
                $meisai = $col->getText();
                break;
              case "comment" :
                $comment = $col->getText();
                break;
              default :
                // none
            }
          }
        }
        list($keitai, $start, $end, $rest, $jitsumu, $keitai2, $start2, $end2, $rest2, $jitsumu2) = array( null, array(), array(), null, null, null, array(), array(), null, null, ); 
        $keitai = substr($meisai, 0, 1);
        $start = array();
        $end = array();
        $start["hour"]= substr($meisai, 1, 2); 
        $start["minute"] = substr($meisai, 3, 2); 
        $end["hour"] = substr($meisai, 5, 2); 
        $end["minute"] = substr($meisai, 7, 2); 
        $start["time"] = $start["hour"] * 60 + $start["minute"];
        $end["time"] = $end["hour"] * 60 + $end["minute"];
        $rest = substr($meisai, 9, 3); 
        if (substr($rest, 0, 1)=="0")
        {   
          $rest = substr($rest, 1, 2); 
        }
        $jitsumu = $end["time"] - $start["time"] - $rest;
        if (strlen($meisai)==24)
        {
          $keitai2 = substr($meisai, 12, 1);
          $start2["hour"]= substr($meisai, 13, 2);
          $start2["minute"] = substr($meisai, 15, 2);
          $end2["hour"] = substr($meisai, 17, 2);
          $end2["minute"] = substr($meisai, 19, 2);
          $start2["time"] = $start2["hour"] * 60 + $start2["minute"];
          $end2["time"] = $end2["hour"] * 60 + $end2["minute"];
          $rest2 = substr($meisai, 21, 3);
          if (substr($rest2, 0, 1)=="0")
          {
            $rest2 = substr($rest2, 1, 2);
          }
          $jitsumu2 = $end2["time"] - $start2["time"] - $rest2;
        }
        if ($keitai=="S")
        {
          $kintai1 = (string) $start['hour'].':'.$start['minute'].' - '.$end['hour'].':'.$end['minute'];
          $rest1 = (string) $rest;
          if ($keitai2 && $keitai2=="Z")
          {
            $kintai2 = (string) $start2['hour'].':'.$start2['minute'].' - '.$end2['hour'].':'.$end2['minute'];
            $rest2 = (string) $rest2;
          }
        }
        if ($keitai=="Z")
        {
          $kintai2 = (string) $start['hour'].':'.$start['minute'].' - '.$end['hour'].':'.$end['minute'];
          $rest2 = (string) $rest;
          if ($keitai2 && $keitai2=="S")
          {
            $kintai1 = (string) $start2['hour'].':'.$start2['minute'].' - '.$end2['hour'].':'.$end2['minute'];
            $rest1 = (string) $rest2;
          }
        }

        $data[] = array(
          'date' => $date,
          'y' => $y,
          'm' => $m,
          'd' => $d,
          'kintai1' => $kintai1,
          'rest1' => $rest1,
          'kintai2' => $kintai2,
          'rest2' => $rest2,
          'comment' => $comment,
          'flag' => 1,
        );
      }
      else
      {
        $data[]= array(
          'date'=> $date,
          'y' => $y,
          'm' => $m,
          'd' => $d,
          'flag' => 0
        );
      }
    }

    return $this->renderText(json_encode(array('status' => 'success', 'data' => $data)));    
  }

  public function executeRegist(sfWebRequest $request)
  {
    $this->nickname = $this->getUser()->getMember()->getName();
  }

  public function executeSend2(sfWebRequest $request)
  {
    $service = self::getZendGdata();
    $wid = self::getRowId($service);
    if ($request->isMethod(sfWebRequest::POST))
    {
      //Definition
      $memberId = $this->getUser()->getMemberId();
      $y = $request->getParameter('y');
      if (empty($y))
      {
        $y = date('Y');
      }
      $m = $request->getParameter('m');
      if (empty($m))
      {
        $m = date('m');
      }
      $d = $request->getParameter('d');
      if (empty($d))
      {
        $d = date('d');
      }
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
      if (substr($rest, 0, 1)=="0")
      {
        $rest = substr($rest, 1, 2);
      }
      $jitsumu = $end["time"] - $start["time"] - $rest;
      
      if (strlen($data)==24)
      { 
        $keitai2 = substr($data, 12, 1);
        $start2["hour"]= substr($data, 13, 2);
        $start2["minute"] = substr($data, 15, 2);
        $end2["hour"] = substr($data, 17, 2);
        $end2["minute"] = substr($data, 19, 2);
        $start2["time"] = $start2["hour"] * 60 + $start2["minute"];
        $end2["time"] = $end2["hour"] * 60 + $end2["minute"];
        $rest2 = substr($data, 21, 3);
        if (substr($rest2, 0, 1)=="0")
        {
          $rest2 = substr($rest2, 1, 2);
        }
        $jitsumu2 = $end2["time"] - $start2["time"] - $rest2;
      }

      //Validation
      $message = null;
      if (strlen($data)!=12 && strlen($data)!=24)
      {
        $message.= "入力が不正です。<br />";
      }
      if (!preg_match("/^[0-2][0-9]$/", $start["hour"]) || !preg_match("/^[0-5][0-9]$/", $start["minute"]))
      {
        $message.= "始業時間の入力が誤っています。<br />";
      }
      if (!preg_match("/^[0-2][0-9]$/", $end["hour"]) || !preg_match("/^[0-5][0-9]$/", $end["minute"]))
      {
        $message.= "終業時間の入力が誤っています。<br />";
      }
      if ($jitsumu<=0)
      {
        $message.= "実務時間が0分となってしまいます。入力を見なおしてください。<br />";
      } 
      if (!preg_match("/^\d{2,3}$/", $rest))
      {
        $message.= "休憩時間の入力が誤っています。<br />";
      }
      if ($keitai!="S" && $keitai!="Z")
      {
        $message.= "勤務種別の入力が誤っています。<br />";
      }
 
      if (!$comment)
      {
        $message.= 'コメントがありません。<br />';
      }
      if (strlen($data)==24)
      {
        if (isset($keitai) && isset($keitai2) && $keitai==$keitai2)
        {
          $message.= "同じ業務種別です。(2)";
        }
        if (!preg_match("/^[0-2][0-9]$/", $start2["hour"]) || !preg_match("/^[0-5][0-9]$/", $start2["minute"]))
        {
          $message.= "始業時間の入力が誤っています。(2)<br />";
        }
        if (!preg_match("/^[0-2][0-9]$/", $end2["hour"]) || !preg_match("/^[0-5][0-9]$/", $end2["minute"]))
        {
          $message.= "終業時間の入力が誤っています。(2)<br />";
        }
        if ($jitsumu<=0)
        {
          $message.= "実務時間が0分となってしまいます。入力を見なおしてください。(2)<br />";
        } 
        if (!preg_match("/^\d{2,3}$/", $rest2))
        {
          $message.= "休憩時間の入力が誤っています。(2)<br />";
        }  
        if ($keitai2!="S" && $keitai2!="Z")
        {
          $message.= "勤務種別の入力が誤っています。(2)<br />";
        }
      }

      $unixtime = mktime(0, 0, 0, $m, $d, $y);
      $nowtime = time();
      $pasttime = $unixtime - $nowtime;
      $allowtime = opConfig::get('op_kintai_allowdate', '3');
      if ($pasttime>$allowtime)
      {
        $message.= "勤怠の登録期限がすでに過ぎてしまっています。<br />";
      }

      if (!$message)
      {
        $q = new Zend_Gdata_Spreadsheets_ListQuery();
        $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
        $q->setWorksheetId($wid);
        $query = "id={$memberId} and date={$y}/{$m}/{$d}";
        $q->setSpreadsheetQuery($query);
        $line = $service->getListFeed($q);

        if ($line->entries["0"])
        {
          $message.= "この種別の勤怠はすでに登録済みです。<br />";
        }
      }

      if ($message)
      {
        $arr = array('status' => 'err', 'msg' => $message);
      }
      else
      {
        $ymdhis = "{$y}/{$m}/{$d}";
        $rowData = array(
          'id' => $memberId,
          'date' => $ymdhis,
          'data' => $data,
          'comment' => $comment,
          );
        $arr = array();
        $spdata = $service->insertRow($rowData, opConfig::get('op_kintai_spkey', null), $wid);
        if ($spdata)
        {
          $arr = array('status' => 'ok', 'msg' => '勤怠を保存しました。お疲れ様です。');
        }
        else
        {
          $arr = array('status' => 'err2', 'msg' => '通信エラーです。（スプレッドシートサーバーと通信ができませんでした。）');
        }
      }
      return $this->renderText(json_encode($arr));
    }
    else
    {
      $this->redirect('kintai');
      exit;
    }
  }
  public function executeAjaxRegist(sfWebRequest $request)
  {
    $this->nickname = $this->getUser()->getMember()->getName();
    $y = $request->getParameter('y');
    if (empty($y))
    {
      $y = date('Y');
    }
    $m = $request->getParameter('m');
    if(empty($m))
    {
      $m = date('m');
    }
    $d = $request->getParameter('d');
    if (empty($d))
    {
      $d = date('d');
    }

    $this->data = $request->getParameter('keitai').$request->getParameter('sh').$request->getparameter('sm').$request->getparameter('eh').$request->getParameter('em');
    $this->rest = $request->getParameter('rest');
    $this->comment = $request->getParameter('comment');

    $this->y = $y;
    $this->m = $m;
    $this->d = $d;
    $this->setLayout(false);
    return sfView::SUCCESS;
  }


  public function executeAjaxRegistEasy(sfWebRequest $request)
  {
    $this->nickname = $this->getUser()->getMember()->getName();
    $y = $request->getParameter('y');
    if (empty($y))
    {
      $y = date('Y');
    }
    $m = $request->getParameter('m');
    if (empty($m))
    {
      $m = date('m');
    }
    $d = $request->getParameter('d');
    if (empty($d))
    {
      $d = date('d');
    }
    $this->y = $y;
    $this->m = $m;
    $this->d = $d;
    $this->setLayout(false);
    return sfView::SUCCESS;
  }


  public function executeAjaxEdit(sfWebRequest $request)
  {
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
    if(!$listFeed->entries["0"])
    {
      return $this->renderText("この日の勤怠は存在しないか、既に編集不可能です。");
    }
    else
    {
      foreach ($listFeed->entries as $entry)
      {
        $line_list = $entry->getCustom();
        foreach ($line_list as $line)
        {
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

  public function executeAjaxSend(sfWebRequest $request)
  {
    $service = self::getZendGdata();
    $wid = self::getRowId($service);
    if ($request->isMethod(sfWebRequest::POST))
    {
      $y = $request->getParameter('y');
      $m = $request->getParameter('m');
      $d = $request->getParameter('d');
      $memberId = $this->getUser()->getMemberId();
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
      if (substr($rest, 0, 1)=="0")
      {
        $rest = substr($rest, 1, 2);
      }
      $jitsumu = $end["time"] - $start["time"] - $rest;
      
      if (strlen($data)==24)
      { 
        $keitai2 = substr($data, 12, 1);
        $start2["hour"]= substr($data, 13, 2);
        $start2["minute"] = substr($data, 15, 2);
        $end2["hour"] = substr($data, 17, 2);
        $end2["minute"] = substr($data, 19, 2);
        $start2["time"] = $start2["hour"] * 60 + $start2["minute"];
        $end2["time"] = $end2["hour"] * 60 + $end2["minute"];
        $rest2 = substr($data, 21, 3);
        if (substr($rest2, 0, 1)=="0")
        {
          $rest2 = substr($rest2, 1, 2);
        }
        $jitsumu2 = $end2["time"] - $start2["time"] - $rest2;
      }

      //Validation
      $message = null;
      if (strlen($data)!=12 && strlen($data)!=24)
      {
        $message.= "入力が不正です。<br />";
      }
      if (!preg_match("/^[0-2][0-9]$/", $start["hour"]) || !preg_match("/^[0-5][0-9]$/", $start["minute"]))
      {
        $message.= "始業時間の入力が誤っています。<br />";
      }
      if (!preg_match("/^[0-2][0-9]$/", $end["hour"]) || !preg_match("/^[0-5][0-9]$/", $end["minute"]))
      {
        $message.= "終業時間の入力が誤っています。<br />";
      }
      if ($jitsumu<=0)
      {
        $message.= "実務時間が0分となってしまいます。入力を見なおしてください。<br />";
      } 
      if (!preg_match("/^\d{2,3}$/", $rest))
      {
        $message.= "休憩時間の入力が誤っています。<br />";
      }
      if ($keitai!="S" && $keitai!="Z")
      {
        $message.= "勤務種別の入力が誤っています(2)。<br />";
      }
 
      if (!$comment)
      {
        $message.= 'コメントがありません。<br />';
      }
      if (strlen($data)==24)
      {
        if (isset($keitai) && isset($keitai2) && $keitai==$keitai2)
        {
          $message.= "同じ業務種別です。(2)";
        }
        if (!preg_match("/^[0-2][0-9]$/", $start2["hour"]) || !preg_match("/^[0-5][0-9]$/", $start2["minute"]))
        {
          $message.= "始業時間の入力が誤っています。(2)<br />";
        }
        if (!preg_match("/^[0-2][0-9]$/", $end2["hour"]) || !preg_match("/^[0-5][0-9]$/", $end2["minute"]))
        {
          $message.= "終業時間の入力が誤っています。(2)<br />";
        }
        if ($jitsumu<=0)
        {
          $message.= "実務時間が0分となってしまいます。入力を見なおしてください。(2)<br />";
        } 
        if (!preg_match("/^\d{2,3}$/", $rest2))
        {
          $message.= "休憩時間の入力が誤っています。(2)<br />";
        }  
        if ($keitai2!="S" && $keitai2!="Z")
        {
          $message.= "勤務種別の入力が誤っています。(2)<br />";
        }
      }

      $unixtime = mktime(0, 0, 0, $m, $d, $y);
      $nowtime = time();
      $pasttime = $unixtime - $nowtime;
      $allowtime = opConfig::get('op_kintai_allowdate', '3');
      if ($pasttime>$allowtime)
      {
        $message.= "勤怠の登録期限がすでに過ぎてしまっています。<br />";
      }

      $q = new Zend_Gdata_Spreadsheets_ListQuery();
      $q->setSpreadsheetKey(opConfig::get('op_kintai_spkey', null));
      $q->setWorksheetId($wid);
      $query = "id={$memberId} and date={$y}/{$m}/{$d}";
      $q->setSpreadsheetQuery($query);
      $line = $service->getListFeed($q);

      if (!$line->entries["0"])
      {
        $message.= '編集しようとした勤怠は存在しませんでした。<br />';
      }
      else
      {
        $nowtime = time();
        $unixtime = mktime(0, 0, 0, $m, $d, $y);
        $pasttime = $nowtime - $unixtime;
        $allowtime = opConfig::get('op_kintai_allowdate', '3') * 24 * 60 * 60;
        if ($pasttime > $allowtime)
        {   // 259200 = 3 * 24 * 60 * 60
            $message.= "この勤怠はすでに編集不可となっています。<br />";
        }
      }

      if ($message)
      {
        $arr = array('status' => 'err', 'msg' => $message);
      }
      else
      {
        $ymdhis = "{$y}/{$m}/{$d}";
        $rowData = array(
          'id' => $memberId,
          'date' => $ymdhis,
          'data' => $data,
          'comment' => $comment,
        );
        $arr = array();
        $spdata = $service->updateRow($line->entries['0'], $rowData);
        if ($spdata)
        {
          $arr = array('status' => 'ok', 'msg' => '勤怠を編集しました。<br />');
        }
        else
        {
          $arr = array('status' => 'err2', 'msg' => '通信エラーです。（スプレッドシートサーバーと通信ができませんでした。）');
        }
      }
      return $this->renderText(json_encode($arr));
    }
    else
    {
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

  private function getMemberSpreadSheetKey($service, $memberId)
  {
    $member = Doctrine::getTable('Member')->find($memberId);
    $memberEmailAddress = $member->getEmailAddress(false);
    $spreadsheetname = $memberEmailAddress."-kintai";
    $feed = $service->getSpreadsheetFeed();
    $i = 0;
    foreach ($feed->entries as $entry)
    {
      if ($entry->title->text===$spreadsheetname)
      {
        $aKey = split('/', $feed->entries[$i]->id->text);
        $SpreadsheetKey = $aKey[5];
        break;
      }
      $i++;
    }
    if ($SpreadsheetKey)
    {
      return $SpreadsheetKey;
    }
    else
    {
      return null;
    }
  }

  private function getMemberWorkSheetId($service, $spreadsheetKey)
  {
    $worksheetname = "勤怠明細";
    $documentQuery = new Zend_Gdata_Spreadsheets_DocumentQuery();
    $documentQuery->setSpreadsheetKey($spreadsheetKey);
    $spreadsheetFeed = $service->getWorksheetFeed($documentQuery);
    $i = 0;
    foreach ($spreadsheetFeed->entries as $worksheetEntry)
    {
      $worksheetIds = split('/', $spreadsheetFeed->entries[$i]->id->text);
      if ($worksheetEntry->title->text===$worksheetname)
      {
         $worksheetId = $worksheetIds[8];
         break;
      }
      $i++;
    }
    return $worksheetId;
  }

  private function getMemberMasterSpreadSheetKey($service, $memberId)
  {
    $member = Doctrine::getTable('Member')->find($memberId);
    $memberEmailAddress = $member->getEmailAddress(false);
    $spreadsheetname = "(Master) ".$memberEmailAddress."-kintai";
    $feed = $service->getSpreadsheetFeed();
    $i = 0;
    foreach ($feed->entries as $entry)
    {
      if ($entry->title->text===$spreadsheetname)
      {
        $aKey = split('/', $feed->entries[$i]->id->text);
        $spreadsheetKey = $aKey[5];
        break;
      }
      $i++;
    }
    if ($spreadsheetKey)
    {
      return $spreadsheetKey;
    }
    else
    {
      return null;
    }
  }

  private function getMemberMasterWorkSheetId($service, $spreadsheetKey)
  {
    $worksheetname = "勤怠明細";
    $documentQuery = new Zend_Gdata_Spreadsheets_DocumentQuery();
    $documentQuery->setSpreadsheetKey($spreadsheetKey);
    $spreadsheetFeed = $service->getWorksheetFeed($documentQuery);
    $i = 0;
    foreach ($spreadsheetFeed->entries as $worksheetEntry)
    {
      $worksheetIds = split('/', $spreadsheetFeed->entries[$i]->id->text);
      if($worksheetEntry->title->text===$worksheetname){
         $worksheetId = $worksheetIds[8];
         break;
      }
      $i++;
    }
    return $worksheetId;
  }


  private function getRowId($service){
    $worksheetname = "RAW";
    $documentQuery = new Zend_Gdata_Spreadsheets_DocumentQuery();
    $documentQuery->setSpreadsheetKey(opConfig::get('op_kintai_spkey'));
    $spreadsheetFeed = $service->getWorksheetFeed($documentQuery);
    $i = 0;
    foreach ($spreadsheetFeed->entries as $worksheetEntry)
    {
      $worksheetIds = split('/', $spreadsheetFeed->entries[$i]->id->text);
      if ($worksheetEntry->title->text===$worksheetname)
      {
         $worksheetId = $worksheetIds[8];
         break;
      }
      $i++;
    }
    return $worksheetId;
  }
}
