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

namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CookieTokenRepository extends EntityRepository
{
    /**
     * Get or if not found create a CookieToken object by the token.
     * If a new CookieToken is created also a new Melder object will be created.
     *
     * @param string $token
     * @return \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\CookieToken|NULL
     */
    public function getOrCreate($token)
    {
        $cookieToken = $this->findOneBy(['token' => $token]);

        if ($cookieToken === null) {
            $cookieToken = new CookieToken();
            $cookieToken->setToken($token);
            $cookieToken->setIsNew();

            $melder = new Melder();
            $melder->addCookieToken($cookieToken);

            $this->_em->persist($cookieToken);
            $this->_em->persist($melder);
            $this->_em->flush();
        }

        return $cookieToken;
    }
}