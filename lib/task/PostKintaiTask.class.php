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

  public function execute($arguments = array(), $options = array())
  {
    echo "START KINTAI BOT.\n";
    $details = array();
    $databaseManager = 	new sfDatabaseManager($this->configuration);
    $y = date('Y');
    $m = date('m');
    $nowday = date('d');
    $service = self::getZendGdata();
    $members = Doctrine::getTable('Member')->findAll();
    foreach ($members as $member)
    {
      $memberId = $member->getId();
      $memberConfig = self::getMemberWorkSheetId($memberId);
      if ($memberConfig)
      {
        $config = $memberConfig;
        $wid = self::getRowId();
        $q = new Zend_Gdata_Spreadsheets_ListQuery();
        $q->setSpreadsheetkey(opConfig::get('op_kintai_spkey'));
        $q->setWorkSheetId($wid);
        $query = 'id='.$memberId.' and year='.$y.' and month='.$m;
        $q->setSpreadsheetQuery($query);
        $lineList = $service->getListFeed($q);
        foreach ($lineList as $entry)
        {
          $line = $entry->getCustom();
          foreach ($line as $list)
          {
            $key = $list->getColumnName();
            switch ($key)
            {
              case 'year':
                $y = $list->getText();
                break;
              case 'month': 
                $m = $list->getText();
                break;
              case 'date':
                $d = (string)$list->getText();
                break;
              case 'data':
                $data = $list->getText();
                break;
              case 'rest':
                $rest = $list->getText();
                break;
              case 'comment':
                $comment = $list->getText();
                break;
              default:
                break;
            }
          }
          $keitai = substr($data, 0, 1);
          // if ($keitai=='S'){ $keitai = '出社'; }else{ $keitai = '在宅'; }
          $sh = substr($data, 1, 2);
          $sm = substr($data, 3, 2);
          $eh = substr($data, 5, 2);
          $em = substr($data, 7, 2);
          $rh = floor($rest / 60);
          $rm = $rest - ( $rh * 60 );
          $starttime = $sh * 60 + $sm;
          $endtime = $eh * 60 + $em;
          $jitsumu = $endtime - $starttime - $rest;
          $jh = floor($jitsumu / 60);
          $jm = $jitsumu - $jh * 60;
          if (strlen($jh)==1)
          {
            $jh = '0'.$jh;
          }
          if (strlen($jm)==1)
          {
            $jm = '0'.$jm;
          }
          if ($rh==0)
          {
            $rh = '0';
          }
          if ($rm==0)
          {
            $rm = '0';
          }
          if ($keitai=='S')
          {
            $details[$d] = array('year' => $y, 'month' => $m, 'date' => $d, 'ssh' => $sh, 'ssm' => $sm, 'seh' => $eh, 'sem' => $em, 'srh' => $rh, 'srm' => $rm, 'sjh' => $jh, 'sjm' => $jm);
          }
          if ($keitai=='Z')
          {
            $details[$d] = array('year' => $y, 'month' => $m, 'date' => $d, 'zsh' => $sh, 'zsm' => $sm, 'zeh' => $eh, 'zem' => $em, 'zrh' => $rh, 'zrm' => $rm, 'zjh' => $jh, 'zjm' => $jm);
          }
        }

        $month = date('m');
        if ($m==1 || $m==3 || $m==5 || $m==7 || $m==8 || $m==10 || $m==12)
        {
          $maxday = 31;
        }
        elseif ($m==2)
        {
          if (($year %4 == 0 && $year %100 != 0) || $year %400 == 0)
          {
            $maxday = 29;
          }
          else
          {
            $maxday = 28;
          }
        }
        else
        {
          $maxday = 30;
        }

        for ($i=1;$i<=$maxday;$i++)
        {
          if (is_null($details[$i]))
          {
            unset($details[$i]);
            $details[$i] = array('year' => $y, 'month' => $m, 'date' => $i);
          }  
        }
        // var_dump($details);
        foreach ($details as $detail)
        {
          $s = new Zend_Gdata_Spreadsheets_ListQuery();
          $s->setSpreadsheetkey(opConfig::get('op_kintai_spkey'));
          $s->setWorkSheetId($config);
          $query = 'year='.$detail['year'].' and month='.$detail['month'].' and date='.$detail['date'];
          $s->setSpreadsheetQuery($query);
          $lineList = $service->getListFeed($s);
          $update = $service->updateRow($lineList->entries['0'], $detail);
          if ($update)
          {
            echo 'Success! member-id : '.$memberId.'  date: '.$detail['year'].'/'.$detail['month'].'/'.$detail['date']."\n";
          }
        }
        unset($details);
        $lineList = null;
        $line = null;
        // var_dump($details);
      }
    }
  }

  public static function getZendGdata()
  {
    $id = Doctrine::getTable('SnsConfig')->get('op_kintai_spid');
    $pw = Doctrine::getTable('SnsConfig')->get('op_kintai_sppw');
    $service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
    $client = Zend_Gdata_ClientLogin::getHttpClient($id, $pw, $service);

    return new Zend_Gdata_Spreadsheets($client);
  }

  private function getMemberWorkSheetId($memberId)
  {
    $service = self::getZendGdata();
    $member = Doctrine::getTable('Member')->find($memberId);
    $memberEmailAddress = $member->getEmailAddress(false);
    $memberEmailAddressUserName  = explode('@', $memberEmailAddress);
    $worksheetname = $memberEmailAddressUserName[0];
    $documentQuery = new Zend_Gdata_Spreadsheets_DocumentQuery();
    $documentQuery->setSpreadsheetKey(opConfig::get('op_kintai_spkey'));
    $spreadsheetFeed = $service->getWorksheetFeed($documentQuery);
    $i = 0;
    foreach ($spreadsheetFeed->entries as $worksheetEntry)
    {
      $worksheetIdText = split('/', $spreadsheetFeed->entries[$i]->id->text);
      if ($worksheetEntry->title->text===$worksheetname)
      {
        $worksheetId = $worksheetIdText[8];
        break;
      }
      $i++;
    }

    return $worksheetId;
  }

  private function getRowId()
  {
    $service = self::getZendGdata();
    $worksheetname = 'RAW';
    $documentQuery = new Zend_Gdata_Spreadsheets_DocumentQuery();
    $documentQuery->setSpreadsheetKey(opConfig::get('op_kintai_spkey'));
    $spreadsheetFeed = $service->getWorksheetFeed($documentQuery);
    $i = 0;
    foreach ($spreadsheetFeed->entries as $worksheetEntry)
    {
      $worksheetIdText = split('/', $spreadsheetFeed->entries[$i]->id->text);
      if ($worksheetEntry->title->text===$worksheetname)
      {
        $worksheetId = $worksheetIdText[8];
        break;
      }
      $i++;
    }

    return $worksheetId;
  }

}


