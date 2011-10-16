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
      $member = Doctrine::getTable('Member')->find($memberId, null);
      if($member==null)
      {
        return sfView::ERROR;
      }else{
        $this->member = $member;
      }
    }else{
      $member = Doctrine::getTable('Member')->findAll();
    }
      
  }

  public function executeEdit(sfWebRequest $request)
  {
    if($request->isMethod(sfWebRequest::POST))
    {
      $memberId = $request->getParameter('member_id');
      $spkey = $request->getParameter('spkey');
      if(!$memberId || !$spkey)
      {
        return sfView::ERROR;
      }else{
        $member = Doctrine::getTable('Member')->find($member_id, null);
        if($member==null){
          return sfView::ERROR;
        }else{
          $config = Doctrine::getTable('MemberConfig')->retrieveByNameAndMemberId('kintai_spreadsheet_key', $memberId);
          if(!$config) {
            $config = new MemberConfig();
            $config->setName('kintai_spreadsheet_key');
            $config->setMember($member);
          }
          $config->setValue($spkey);
          $config->save();
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
      $config = Doctrine::getTable('memberConfig')->retrieveByNameAndMemberId('kintai_spreadsheet_key', $memberId);
      $this->value = $config->getValue();
    }
  }

}
