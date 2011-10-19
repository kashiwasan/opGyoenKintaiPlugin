<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opGyoenKintaiPlugin actions.
 *
 * @package    OpenPNE
 * @subpackage opGyoenKintaiPlugin
 * @author     Shouta Kashiwagi <kashiwagi@tejimaya.com>
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class opGyoenKintaiPluginActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new GyoenKintaiConfigForm();
    
    if($request->isMethod(sfWebRequest::POST))
    {
      //$this->form->getCSRFToken();
      $this->form->bind($request->getParameter('kintai_config'));
      if($this->form->isValid())
      {
        $this->form->save();
        $this->redirect('opGyoenKintaiPlugin/index');
      }
    }
    return sfView::SUCCESS;
  }

  public function executeList(sfWebRequest $request)
  {
    if($request->isMethod(sfWebRequest::POST))
    {
      $memberId = $request->getParameter('member_id');
      $members = Doctrine::getTable('Member')->find($memberId, null);
      if($members==null)
      {
        return sfView::ERROR;
      }else{
        $this->members = $members;
        return sfView::SUCCESS;
      }
   
    }else{
      $this->members = Doctrine::getTable('Member')->findAll();
      return sfView::SUCCESS;
    }
      
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->form = new sfForm();
    if($request->isMethod(sfWebRequest::POST))
    {
      $memberId = $request->getParameter('member_id');
      $wid = $request->getParameter('wid');
      if(!$memberId || !$wid)
      {
        return sfView::ERROR;
      }else{
        $member = Doctrine::getTable('Member')->find($memberId, null);
        if($member==null){
          return sfView::ERROR;
        }else{
          $config = Doctrine::getTable('MemberConfig')->retrieveByNameAndMemberId('op_kintai_member_wid', $memberId);
          if(!$config) {
            $config = new MemberConfig();
            $config->setName('op_kintai_member_wid');
            $config->setMember($member);
          }
          $config->setValue($wid);
          $config->save();
          $this->message = "登録しました。";
          $this->member = $member;
          $this->value = $wid;
          return sfView::SUCCESS;
        }
      }

    }else{
      $memberId = $request->getParameter('member_id');
      if($memberId==null)
      {
         $this->redirect('opGyoenKintaiPlugin/list');
         exit;
      }
      $member = Doctrine::getTable('Member')->find($memberId, null);
      if($member==null)
      {
         $this->redirect('opGyoenKintaiPlugin/list');
         exit;
      }
      $this->member = $member;
      $config = Doctrine::getTable('MemberConfig')->retrieveByNameAndMemberId('op_kintai_member_wid', $memberId);
      if($config){
        $this->value= $config->getValue();
      }else{
        $this->value = "";
      }
      return sfView::SUCCESS;
    }
  }
}
