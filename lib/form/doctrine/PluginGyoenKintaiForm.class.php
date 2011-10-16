abstract class PluginGyoenKintaiForm extends BaseGyoenKintaiForm
{
  public function setup()
  {
    parent::setup();
    $this->useFields(array('comment'));
  }
}
