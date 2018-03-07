<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SV\SpamTriggerPruneConfig\XF\Repository;

/**
 * Extends \XF\Repository\Spam
 */
class Spam extends XFCP_Spam
{
    const RESULT_ALLOWED   = 'allowed';
    const RESULT_MODERATED = 'moderated';
    const RESULT_DENIED    = 'denied';

    public function cleanupSpamTriggerLog($cutOff = null)
    {
        $db = $this->db();
        $options = \XF::options();
        $time = \XF::$time;
        if ($cutOff === null)
        {
            $cutOff = intval($options->svSpamTriggerNonUser) * 86400;
        }

        if ($cutOff > 0)
        {
            $db->query("DELETE FROM xf_spam_trigger_log WHERE content_type <> 'user' AND log_date < ? ",
                       [$time - $cutOff]);
        }
        $cutOff = intval($options->svSpamTriggerRejectedUser);
        if ($cutOff > 0)
        {
            $db->query("DELETE FROM xf_spam_trigger_log WHERE content_type = ? AND result = ? AND log_date < ? ",
                       ['user', self::RESULT_DENIED, $time - $cutOff * 86400]);
        }
        $cutOff = intval($options->svSpamTriggerModeratedUser);
        if ($cutOff > 0)
        {
            $db->query("DELETE FROM xf_spam_trigger_log WHERE content_type = ? AND result = ? AND log_date < ? ",
                       ['user', self::RESULT_MODERATED, $time - $cutOff * 86400]);
        }
        $cutOff = intval($options->svSpamTriggerAllowedUser);
        if (!$cutOff > 0)
        {
            $db->query("DELETE FROM xf_spam_trigger_log WHERE content_type = ? AND result = ? AND log_date < ? ",
                       ['user', self::RESULT_ALLOWED, $time - $cutOff * 86400]);
            // prune spam trigger records for deleted users (should mostly be moderated but rejected)
            $db->query("DELETE FROM xf_spam_trigger_log WHERE content_type = ? AND log_date < ? AND content_id NOT IN (SELECT user_id FROM xf_user WHERE xf_user.user_id = xf_spam_trigger_log.content_id) ",
                       ['user', $time - $cutOff * 86400]);
        }
    }
}