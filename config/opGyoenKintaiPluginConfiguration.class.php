<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opOpenSocialPluginConfiguration
 *
 * @package    opGyoenKintaiPlugin
 * @subpackage config
 * @author     Shouta Kashiwagi <kashiwagi@tejimaya.com>
 */
class opGyoenKintaiPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    sfToolkit::addIncludePath(OPENPNE3_CONFIG_DIR.'/../lib/vendor');
  }
}

