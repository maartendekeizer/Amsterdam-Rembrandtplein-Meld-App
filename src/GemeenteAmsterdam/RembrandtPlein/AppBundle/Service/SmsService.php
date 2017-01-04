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

abstract class SmsService
{
    /**
     * @var string
     */
    protected $defaultFrom;

    /**
     * @var boolean
     */
    protected $useDummy = true;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @param string $defaultForm
     * @param LoggerInterface $logger
     * @param EngineInterface $templating
     * @param boolean $useDummy
     */
    public function __construct($defaultForm, $logger, $templating, $useDummy)
    {
        $this->defaultFrom = $defaultForm;
        $this->logger = $logger;
        $this->templating = $templating;
        $this->useDummy = $useDummy;
    }

    /**
     * @param string $to
     * @param string $template
     * @param array $parameters
     * @param string $from
     * @return boolean
     */
    public function sentSmsFromTemplate($to, $template, $parameters, $from = null)
    {
        $body = $this->templating->render($template, $parameters);

        return $this->sentSms($to, $body, $from);
    }

    /**
     * @param string $to
     * @param string $body
     * @param string $from
     * @return boolean
     */
    public function sentSms($to, $body, $from = null)
    {
        $from = $from === null ? $this->defaultFrom : $from;
        if ($this->useDummy === true) {
            return $this->dummySent($to, $body, $from);
        }
        return $this->sent($to, $body, $from);
    }

    /**
     * @param string $to
     * @param string $body
     * @param string $from
     */
    abstract protected function sent($to, $body, $from);

    /**
     * @param string $to
     * @param string $body
     * @param string $from
     */
    protected function dummySent($to, $body, $from)
    {
        $this->logger->info('Dummy SMS sent', ['to' => $to, 'from' => $from, 'body' => $body]);
        return true;
    }
}