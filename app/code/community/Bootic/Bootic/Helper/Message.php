<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Helper_Message extends Bootic_Bootic_Helper_Abstract
{
    /**
     * Pulls all account messages and store them locally if need be
     */
    public function pullUnreadMessages()
    {
        try {
            $result = $this->getBootic()->getAccountMessages();
        } catch (Exception $e) {
            // We do nothing
            return;
        }

        $messages = array_reverse($result->getData());

        foreach ($messages as $message) {
            $booticMessage = Mage::getModel('bootic/message')->load($message['id']);

            if ($booticMessage->isObjectNew()) {
                $content = 'From <strong>' . $message['user_name'] . '</strong><br/>';
                $content .= $message['content'];

                $magentoMessage = Mage::getModel('adminnotification/inbox');
                $magentoMessage->setSeverity(2);
                $magentoMessage->setTitle($message['subject']);
                $magentoMessage->setDescription($content);
                $magentoMessage->setDateAdded($message['date']);
                $magentoMessage->save();

                $booticMessage->setMagentoMessageId($magentoMessage->getId());
                $booticMessage->setBooticMessageId($message['id']);
                $booticMessage->save();
            }
        }
    }

    /**
     * Marks messages as read
     * @param $ids
     */
    public function markMessageAsRead($ids)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }

        $this->getBootic()->markMessageAsRead(array(
            'msg_ids' => $ids
        ));
    }
}
