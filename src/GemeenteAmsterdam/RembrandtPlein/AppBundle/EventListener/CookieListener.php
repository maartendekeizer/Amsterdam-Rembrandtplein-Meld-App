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

namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use GemeenteAmsterdam\RembrandtPlein\AppBundle\Controller\MelderController;
use GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\CookieTokenRepository;

class CookieListener
{
    /**
     * @var string
     */
    private $cookieName;

    /**
     * @var string
     */
    private $cookieExpiry;

    /**
     * @var string
     */
    private $cookieSecure;

    /**
     * @var CookieTokenRepository
     */
    private $cookieTokenRepository;

    /**
     * @param string $cookieName
     * @param string $cookieExpiry
     * @param string $cookieSecure
     * @param CookieTokenRepository $cookieTokenRepository
     */
    public function __construct($cookieName, $cookieExpiry, $cookieSecure, CookieTokenRepository $cookieTokenRepository)
    {
        $this->cookieName = $cookieName;
        $this->cookieExpiry = $cookieExpiry;
        $this->cookieSecure = $cookieSecure;
        $this->cookieTokenRepository = $cookieTokenRepository;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        // extract some variables
        $request = $event->getRequest();
        $action = $event->getController();

        // apply only on the MelderController
        if (($action[0] instanceof MelderController) === false) {
            return;
        }

        // exclude some actions
        if (in_array($action[1], ['cookieInfoAction']) === true) {
            return;
        }

        // get the token string from the cookie or if not set, create a new 'random' token string
        $tokenString = $request->cookies->get($this->cookieName, uniqid('t', true) . time() . md5($request->getClientIp()));

        // get or create a cookieToken object from the database
        $cookieToken = $this->cookieTokenRepository->getOrCreate($tokenString);

        // add the token to the attributes
        $request->attributes->set('cookieToken', $cookieToken);
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        // extract some variables
        $request = $event->getRequest();
        $response = $event->getResponse();

        // is a cookieToken attribute available on this session?
        if ($request->attributes->has('cookieToken') === true) {
            $cookieToken = $request->attributes->get('cookieToken');

            // set or update the cookie at the client
            $cookie = new Cookie($this->cookieName, $cookieToken->getToken(), time() + $this->cookieExpiry, '/', null, $this->cookieSecure, true);
            $response->headers->setCookie($cookie);
        }
    }
}