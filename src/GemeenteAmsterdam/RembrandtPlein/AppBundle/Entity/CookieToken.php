<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CookieTokenRepository")
 * @ORM\Table(
 *  name="cookie_token",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="uq__cookie_token__token", columns={"token"})
 *  }
 * )
 */
class CookieToken
{
    /**
     * @var integer
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=125, nullable=false)
     */
    private $token;

    /**
     * @var Melder
     * @ORM\ManyToOne(targetEntity="Melder", inversedBy="cookieTokens")
     * @ORM\JoinColumn(name="melder_id", referencedColumnName="id")
     */
    private $melder;

    /**
     * Non mapped value, true if just created
     * @var boolean
     */
    private $isNew = false;

    /**
     * @return number
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Melder
     */
    public function getMelder()
    {
        return $this->melder;
    }

    /**
     * @return boolean
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @param Melder $melder
     */
    public function setMelder(Melder $melder = null)
    {
        if ($this->melder !== $melder)
            $this->melder = $melder;
        if ($melder !== null)
            $melder->addCookieToken($this);
    }

    /**
     * Set isNew to true
     */
    public function setIsNew()
    {
        $this->isNew = true;
    }
}