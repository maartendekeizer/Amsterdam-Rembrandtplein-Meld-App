<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Templating\EngineInterface;

class CmSmsGatewaySmsService extends SmsService
{
    /**
     * @var string
     */
    protected $producttoken;

    /**
     * @param string $defaultFrom
     * @param LoggerInterface $logger
     * @param EngineInterface $templating
     * @param boolean $useDummy
     * @param string $producttoken
     */
    public function __construct($defaultFrom, LoggerInterface $logger, EngineInterface $templating, $useDummy, $producttoken)
    {
        parent::__construct($defaultFrom, $logger, $templating, $useDummy);
        $this->producttoken = $producttoken;
    }

    /**
     * {@inheritDoc}
     * @see \GemeenteAmsterdam\RembrandtPlein\AppBundle\Service\SmsService::sent()
     */
    public function sent($to, $body, $from)
    {
        if (substr($to, 0, 1) === '+')
            $to = substr($to, 1);
        if (substr($to, 0, 2) === '06')
            $to = '31' . substr($to, 1);
        if (substr($to, 0, 2) !== '00')
            $to = '00' . $to;

        $doc = new \DOMDocument('1.0', 'utf-8');

        $domMessage = $doc->createElement('MESSAGES');
        $doc->appendChild($domMessage);

        $domAuthentification = $doc->createElement('AUTHENTICATION');
        $domMessage->appendChild($domAuthentification);

        $domProducttoken = $doc->createElement('PRODUCTTOKEN');
        $domProducttoken->nodeValue = $this->producttoken;
        $domAuthentification->appendChild($domProducttoken);

        $domMsg = $doc->createElement('MSG');
        $domMessage->appendChild($domMsg);

        $domFrom = $doc->createElement('FROM');
        $domFrom->nodeValue = $from;
        $domMsg->appendChild($domFrom);

        $domTo = $doc->createElement('TO');
        $domTo->nodeValue = $to;
        $domMsg->appendChild($domTo);

        $domBody = $doc->createElement('BODY');
        $domBody->nodeValue = $body;
        $domMsg->appendChild($domBody);

        $domMinimumnumberofmessageparts = $doc->createElement('MINIMUMNUMBEROFMESSAGEPARTS');
        $domMinimumnumberofmessageparts->nodeValue = 1;
        $domMsg->appendChild($domMinimumnumberofmessageparts);

        $domMaximumnumberofmessageparts = $doc->createElement('MAXIMUMNUMBEROFMESSAGEPARTS');
        $domMaximumnumberofmessageparts->nodeValue = 8;
        $domMsg->appendChild($domMaximumnumberofmessageparts);

        $doc->formatOutput = true;
        $xmlContent = $doc->saveXML();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://sgw01.cm.nl/gateway.ashx');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlContent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        if (trim($response) != '') {
            $this->logger->error('Can not sent SMS via CmSmsGateway: ' . $response, ['to' => $to, 'from' => $from, 'body' => $body]);
            return false;
        }

        $this->logger->info('SMS sent via CmSmsGateway', ['to' => $to, 'from' => $from, 'body' => $body]);

        return true;
    }
}