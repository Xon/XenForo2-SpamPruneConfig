<?php

namespace SV\SpamTriggerPruneConfig\XF\Spam;

use XF\Util\Ip;

/**
 * Extends \XF\Spam\ContentChecker
 */
class ContentChecker extends XFCP_ContentChecker
{
    public function logSpamTrigger($contentType, $contentId)
    {
        $ret = parent::logSpamTrigger($contentType, $contentId);
        if ($ret !== false)
        {
            return $ret;
        }
        // XF2 discards allowed results...
        $result = 'allowed';
        $request = $this->app()->request();

        $ipAddress = Ip::convertIpStringToBinary($request->getIp());
        $userId = \XF::visitor()->user_id;

        if (!$contentId)
        {
            $contentId = null;
        }

        if ($contentType == 'user')
        {
            $userId = $contentId ?: 0;
        }

        $request = [
            'url'      => $request->getRequestUri(),
            'referrer' => $request->getServer('HTTP_REFERER', ''),
            '_GET'     => $_GET,
            '_POST'    => $request->filterForLog($_POST)
        ];

        $values = [
            'content_type'  => $contentType,
            'content_id'    => $contentId,
            'log_date'      => time(),
            'user_id'       => $userId,
            'ip_address'    => $ipAddress,
            'result'        => $result,
            'details'       => serialize($this->details),
            'request_state' => serialize($request)
        ];

        $onDupe = [];
        foreach (['log_date', 'user_id', 'ip_address', 'result', 'details', 'request_state'] AS $update)
        {
            $onDupe[] = "$update = VALUES($update)";
        }
        $onDupe = implode(', ', $onDupe);

        $db = $this->app()->db();
        $rows = $db->insert('xf_spam_trigger_log', $values, false, $onDupe);

        return $rows == 1 ? $db->lastInsertId() : true;
    }
}