<?php
/*
 *  Copyright (C) 2017 X Gemeente
 *                     X Amsterdam
 *                     X Onderzoek, Informatie en Statistiek
 *
 *  This Source Code Form is subject to the terms of the Mozilla Public
 *  License, v. 2.0. If a copy of the MPL was not distributed with this
 *  file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Templating\EngineInterface;

class MessageBirdSmsService extends SmsService
{
    /**
     * @var \MessageBird\Client
     */
    protected $messagebirdClient;

    /**
     * @param string $defaultFrom
     * @param LoggerInterface $logger
     * @param EngineInterface $templating
     * @param boolean $useDummy
     * @param \MessageBird\Client $messagebirdClient
     */
    public function __construct($defaultFrom, LoggerInterface $logger, EngineInterface $templating, $useDummy, \MessageBird\Client $messagebirdClient)
    {
        parent::__construct($defaultFrom, $logger, $templating, $useDummy);
        $this->messagebirdClient = $messagebirdClient;
    }

    /**
     * {@inheritDoc}
     * @see \GemeenteAmsterdam\RembrandtPlein\AppBundle\Service\SmsService::sent()
     */
    public function sent($to, $body, $from)
    {
        $message = new \MessageBird\Objects\Message();
        $message->originator = $from;
        $message->recipients = [$to];
        $message->body = $body;

        try {
            $response = $this->messagebirdClient->messages->create($message);
            if ($response === false) {
                $this->logger->error('Can not sent SMS via MessageBird: ' . $response);
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error('Can not sent SMS via MessageBird: ' . $e->getMessage());
        }

        $this->logger->info('SMS sent via MessageBird', ['to' => $to, 'from' => $from, 'body' => $body]);

        return true;
    }

}