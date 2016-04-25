<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="melder")
 */
class Melder
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $naam;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    private $mobielNummer;

    /**
     * @var CookieToken[]
     * @ORM\OneToMany(targetEntity="CookieToken", mappedBy="melder")
     */
    private $cookieTokens;

    /**
     * @var Melding[]
     * @ORM\OneToMany(targetEntity="Melding", mappedBy="melder")
     */
    private $meldingen;

    /**
     * Init a Melder object
     */
    public function __construct()
    {
        $this->cookieTokens = new ArrayCollection();
        $this->meldingen = new ArrayCollection();
    }

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
    public function getNaam()
    {
        return $this->naam;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getMobielNummer()
    {
        return $this->mobielNummer;
    }

    /**
     * @return \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\CookieToken[]
     */
    public function getCookieTokens()
    {
        return $this->cookieTokens;
    }

    /**
     * @return \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Melding[]
     */
    public function getMeldingen()
    {
        return $this->meldingen;
    }

    /**
     * @param string $naam
     */
    public function setNaam($naam)
    {
        $this->naam = $naam;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param string $mobielNummer
     */
    public function setMobielNummer($mobielNummer)
    {
        $this->mobielNummer = $mobielNummer;
    }

    /**
     * @param CookieToken $cookieToken
     */
    public function addCookieToken(CookieToken $cookieToken)
    {
        if ($this->hasCookieToken($cookieToken) === false)
            $this->cookieTokens->add($cookieToken);
        if ($cookieToken->getMelder() !== $this)
            $cookieToken->setMelder($this);
    }

    /**
     * @param CookieToken $cookieToken
     * @return boolean
     */
    public function hasCookieToken(CookieToken $cookieToken)
    {
        return $this->cookieTokens->contains($cookieToken);
    }

    /**
     * @param CookieToken $cookieToken
     */
    public function removeCookieToken(CookieToken $cookieToken)
    {
        if ($this->hasCookieToken($cookieToken) === true)
            $this->cookieTokens->removeElement($cookieToken);
        if ($cookieToken->getMelder() === $this)
            $cookieToken->setMelder(null);
    }

    /**
     * @param Melding $melding
     */
    public function addMelding(Melding $melding)
    {
        if ($this->hasMelding($melding) === false)
            $this->meldingen->add($melding);
        if ($melding->getMelder() !== $this)
            $melding->setMelder($this);
    }

    /**
     * @param Melding $melding
     * @return boolean
     */
    public function hasMelding(Melding $melding)
    {
        return $this->meldingen->contains($melding);
    }

    /**
     * @param Melding $melding
     */
    public function removeMelding(Melding $melding)
    {
        if ($this->hasMelding($melding) === true)
            $this->meldingen->removeElement($melding);
        if ($melding->getMelder() === $this)
            $melding->setMelder(null);
    }

    /**
     * @return boolean
     */
    public function hasNotificatieInformatie()
    {
        return ($this->email !== '' && $this->email !== null) || ($this->mobielNummer !== '' && $this->mobielNummer !== null);
    }
}