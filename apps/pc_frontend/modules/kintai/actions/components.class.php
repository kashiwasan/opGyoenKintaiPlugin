<?php

/**
 * opGyoenKintaiPlugin components.
 *
 * @package    OpenPNE
 * @subpackage opGyoenKintaiPlugin
 * @author     Shouta Kashiwagi
 */

class kintaiComponents extends sfComponents
{
  public function executeKintaiGadget(sfWebRequest $request)
  {
    $this->getResponse()->addJavascript('/opGyoenKintaiPlugin/js/jquery-1.6.4.min.js', 'first');
    $this->getResponse()->addJavascript('/opGyoenKintaiPlugin/js/jquery.prettyPopin.js', 'first');
    $this->getResponse()->addJavascript('/opGyoenKintaiPlugin/js/jquery.kintai.js');
    $this->memberName = $this->getUser()->getMember()->getName();

  }
}


