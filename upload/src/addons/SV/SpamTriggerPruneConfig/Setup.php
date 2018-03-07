<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SV\SpamTriggerPruneConfig;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

/**
 * Add-on installation, upgrade, and uninstall routines.
 */
class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1()
    {
    }

    public function upgrade2000000Step1()
    {
        $this->renameOption('SV_SpamPruneConfig_Prune_Default', 'svSpamTriggerNonUser');
        $this->renameOption('SV_SpamPruneConfig_Prune_User_Allowed', 'svSpamTriggerAllowedUser');
        $this->renameOption('SV_SpamPruneConfig_Prune_User_Moderated', 'svSpamTriggerModeratedUser');
        $this->renameOption('SV_SpamPruneConfig_Prune_User_Rejected', 'svSpamTriggerRejectedUser');
    }

    public function uninstallStep1()
    {
    }

    protected function renameOption($old, $new)
    {
        /** @var \XF\Entity\Option $optionOld */
        $optionOld = \XF::finder('XF:Option')->whereId($old)->fetchOne();
        $optionNew = \XF::finder('XF:Option')->whereId($new)->fetchOne();
        if ($optionOld && !$optionNew)
        {
            $optionOld->option_id = $new;
            $optionOld->save();
        }
    }
}
