<?php
class GyoenKintaiConfigForm extends sfForm
{
  protected $configs = array(
    'spsheet_id'  => 'op_kintai_spid',
    'spsheet_pw'  => 'op_kintai_sppw',
    'spsheet_key' => 'op_kintai_spkey',
  );

  public function configure()
  {
    $this->setWidgets(array(
      'spsheet_id'  => new sfWidgetFormInputText(),
      'spsheet_pw'  => new sfWidgetFormInputPassword(),
      'spsheet_key' => new sfWidgetFormInputText(),
    ));

    foreach($this->configs as $k => $v)
    {
      $config = Doctrine::getTable('SnsConfig')->retrieveByName($v);
      if($config) 
      {
        $this->getWidgetSchema()->setDefault($k, $config->getValue());
      }    
    }

    $this->setValidators(array(
      'spsheet_id'  => new sfValidatorString(array('max_length' => 60)),
      'spsheet_pw'  => new sfValidatorString(array('max_length' => 40)),
      'spsheet_key' => new sfValidatorString(array('max_length' => 100)),
    ));

    $this->widgetSchema->setHelp('spsheet_id', '勤怠記録用のGoogleAppsユーザーIDを入力します。');
    $this->widgetSchema->setHelp('spsheet_pw', '勤怠記録用のGoogleAppsログインパスワードを入力します。');
    $this->widgetSchema->setHelp('spsheet_key', '勤怠記録用のSpreadSheetの固有IDを入力します。');
    $this->getWidgetSchema()->setNameFormat('kintai_config[%s]');
  }


  public function save()
  {
    foreach ($this->getValues() as $k => $v)
    {
      if (!isset($this->configs[$k]))
      {
        continue;
      }

      $config = Doctrine::getTable('SnsConfig')->retrieveByName($this->configs[$k]);
      if (!$config)
      {
        $config = new SnsConfig();
        $config->setName($this->configs[$k]);
      }
      $config->setValue($v);
      $config->save();
    }
  }
}

