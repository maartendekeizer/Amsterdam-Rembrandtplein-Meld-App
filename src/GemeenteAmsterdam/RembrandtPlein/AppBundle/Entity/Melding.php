<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="MeldingRepository")
 * @ORM\Table(
 *  name="melding",
 *  indexes={
 *      @ORM\Index(name="ix__melding__aanmaak_datumtijd", columns={"aanmaak_datumtijd"}),
 *      @ORM\Index(name="ix__melding__melder_id__aanmaak_datumtijd", columns={"melder_id", "aanmaak_datumtijd"}),
 *      @ORM\Index(name="ix__melding__is_verstuurd", columns={"is_verstuurd"}),
 *      @ORM\Index(name="ix__melding__is_gelezen", columns={"is_verstuurd"})
 *  }
 * )
 */
class Melding
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=36, nullable=false)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $secret;

    /**
     * @var Melder
     * @ORM\ManyToOne(targetEntity="Melder", inversedBy="meldingen")
     * @ORM\JoinColumn(name="melder_id", referencedColumnName="id")
     */
    private $melder;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $reactieVanMelderToestaan;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $aanmaakDatumtijd;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $gewijzigdDatumtijd;

    /**
     * @var Reactie[]
     * @ORM\OneToMany(targetEntity="Reactie", mappedBy="melding")
     * @ORM\OrderBy({"aanmaakDatumtijd" = "ASC", "id" = "ASC"})
     */
    private $reacties;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $isVerstuurd;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datumtijdVerstuurd;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $isGelezen;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datumtijdGelezen;

    /**
     * @var Reactie
     * @ORM\ManyToOne(targetEntity="Reactie")
     * @ORM\JoinColumn(name="laatste_bericht_reactie_id", nullable=true)
     */
    private $laatsteBerichtReactie;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    private $aantalBerichtReacties;

    /**
     * @var Handhaver
     * @ORM\ManyToOne(targetEntity="Handhaver")
     * @ORM\JoinColumn(name="handhaver_id", referencedColumnName="id", nullable=false)
     */
    private $initieleBehandelaar;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $melderNotificaties;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $categorie;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $locatie;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adres;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $coordinaten;

    /**
     * Init Melding object
     */
    public function __construct()
    {
        $this->reacties = new ArrayCollection();
        $this->aanmaakDatumtijd = new \DateTime();
        $this->gewijzigdDatumtijd = clone $this->aanmaakDatumtijd;
        $this->isGelezen = false;
        $this->isVerstuurd = false;
        $this->aantalBerichtReacties = 0;
        $this->reactieVanMelderToestaan = false;
        $this->melderNotificaties = false;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
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
    public function getReactieVanMelderToestaan()
    {
        return $this->reactieVanMelderToestaan;
    }

    /**
     * @return \DateTime
     */
    public function getAanmaakDatumtijd()
    {
        return $this->aanmaakDatumtijd;
    }

    /**
     * @return \DateTime
     */
    public function getGewijzigdDatumtijd()
    {
        return $this->gewijzigdDatumtijd;
    }

    /**
     * Get all reacties without the first one (the initiator)
     * @return \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Reactie[]
     */
    public function getReacties()
    {
        return $this->reacties->slice(1, null);
    }

    /**
     * Get the first reactie (the initiator)
     * @return \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Reactie
     */
    public function getFirstReactie()
    {
        return $this->reacties->first();
    }

    /**
     * Get all reacties (including the first one)
     * @return \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Reactie[]
     */
    public function getAllReacties()
    {
        return $this->reacties;
    }

    /**
     * @return boolean
     */
    public function isVerstuurd()
    {
        return $this->isVerstuurd;
    }

    /**
     * @return \DateTime
     */
    public function getDatumtijdVerstuurd()
    {
        return $this->datumtijdVerstuurd;
    }

    /**
     * @return boolean
     */
    public function isGelezen()
    {
        return $this->isGelezen;
    }

    /**
     * @return \DateTime
     */
    public function getDatumtijdGelezen()
    {
        return $this->datumtijdGelezen;
    }

    /**
     * @return number
     */
    public function getAantalBerichtReacties()
    {
        return $this->aantalBerichtReacties;
    }

    /**
     * @return Reactie|NULL
     */
    public function getLaatsteBerichtReactie()
    {
        return $this->laatsteBerichtReactie;
    }

    /**
     * @return Handhaver
     */
    public function getInitieleBehandelaar()
    {
        $this->initieleBehandelaar;
    }

    /**
     * @return boolean
     */
    public function getMelderNotificaties()
    {
        return $this->melderNotificaties;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
        $this->setGewijzigd();
    }

    /**
     * @param Melder $melder
     */
    public function setMelder(Melder $melder = null)
    {
        if ($this->melder !== $melder) {
            $this->melder = $melder;
            $this->setGewijzigd();
        }
        if ($melder !== null)
            $melder->addMelding($this);
    }

    /**
     * @return void
     */
    public function setGewijzigd()
    {
        $this->gewijzigdDatumtijd = new \DateTime();
    }

    /**
     * @param boolean $reactieVanMelderToestaan
     */
    public function setReactieVanMelderToestaan($reactieVanMelderToestaan)
    {
        $this->reactieVanMelderToestaan = $reactieVanMelderToestaan;
        $this->setGewijzigd();
    }

    /**
     * @param Reactie $reactie
     */
    public function addReactie(Reactie $reactie)
    {
        if ($this->hasReactie($reactie) === false) {
            $this->reacties->add($reactie);
            $this->setGewijzigd();

            if ($reactie->getType() === Reactie::TYPE_BERICHT && $this->getFirstReactie() !== $reactie) { // IMPORTANT exclude first
                // incr. count
                $this->aantalBerichtReacties ++;

                // set reactie as last
                if ($this->laatsteBerichtReactie === null || $reactie->getAanmaakDatumtijd() > $this->laatsteBerichtReactie->getAanmaakDatumtijd()) {
                    $this->laatsteBerichtReactie = $reactie;
                }
            }
        }
        if ($reactie->getMelding() !== $this)
            $reactie->setMelding($this);
    }

    /**
     * @param Reactie $reactie
     * @return boolean
     */
    public function hasReactie(Reactie $reactie)
    {
        return $this->reacties->contains($reactie);
    }

    /**
     * @param Reactie $reactie
     */
    public function removeReactie(Reactie $reactie)
    {
        if ($this->hasReactie($reactie) === true) {
            $this->reacties->removeElement($reactie);
            $this->setGewijzigd();

            if ($reactie->getType() === Reactie::TYPE_BERICHT) {
                // decr. count
                $this->aantalBerichtReacties --;

                // set the last reactie (note $this->reacties is sorted!)
                $newLaatsteBerichtReactie = null;
                foreach ($this->reacties as $r) {
                    if ($r->getType() === Reactie::TYPE_BERICHT) {
                        $newLaatsteBerichtReactie = $r;
                    }
                }
                if ($this->getFirstReactie() === $newLaatsteBerichtReactie) { // IMPRORTANT exclude first
                    $newLaatsteBerichtReactie = null;
                }
                $this->laatsteBerichtReactie = $newLaatsteBerichtReactie;
            }
        }
        if ($reactie->getMelding() === $this)
            $reactie->setMelding(null);
    }

    /**
     * @return void
     */
    public function setVerstuurd()
    {
        $this->isVerstuurd = true;
        $this->datumtijdVerstuurd = new \DateTime();
        $this->setGewijzigd();
    }

    /**
     * @return void
     */
    public function setGelezen()
    {
        $this->isGelezen = true;
        $this->datumtijdGelezen = new \DateTime();
        $this->setGewijzigd();
    }

    /**
     * @param Handhaver $initieleBehandelaar
     */
    public function setInitieleBehandelaar(Handhaver $initieleBehandelaar = null)
    {
        $this->initieleBehandelaar = $initieleBehandelaar;
        $this->setGewijzigd();
    }

    /**
     * @param boolean $melderNotificaties
     */
    public function setMelderNotificaties($melderNotificaties)
    {
        $this->melderNotificaties = $melderNotificaties;
    }

    /**
     * @return string
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * @param string $categorie
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;
    }

    /**
     * @return string
     */
    public function getLocatie()
    {
        return $this->locatie;
    }

    /**
     * @param string $locatie
     */
    public function setLocatie($locatie)
    {
        $this->locatie = $locatie;
    }

    /**
     * @return string
     */
    public function getAdres()
    {
        return $this->adres;
    }

    /**
     * @param string $adres
     */
    public function setAdres($adres)
    {
        $this->adres = $adres;
    }


    /**
     * @return string
     */
    public function getCoordinaten()
    {
        return $this->coordinaten;
    }

    /**
     * @param string $coordinaten
     */
    public function setCoordinaten($coordinaten)
    {
        $this->coordinaten = $coordinaten;
    }
}