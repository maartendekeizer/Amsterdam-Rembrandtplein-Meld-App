<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="reactie")
 */
class Reactie
{
    /**
     * @var string
     */
    const AFZENDER_MELDER = 'Melder';

    /**
     * @var string
     */
    const AFZENDER_HANDHAVER = 'Handhaver';

    /**
     * @var string
     */
    const AFZENDER_SYSTEEM = 'Systeem';

    /**
     * @var string
     */
    const TYPE_STATUS_VERSTUURD = 'Verstuurd';

    /**
     * @var string
     */
    const TYPE_STATUS_GELEZEN = 'Gelezen';

    /**
     * @var string
     */
    const TYPE_STATUS_REACTIE_TOESTAAN = 'ReactieToestaan';

    /**
     * @var string
     */
    const TYPE_BERICHT = 'Bericht';

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Melding
     * @ORM\ManyToOne(targetEntity="Melding", inversedBy="reacties")
     * @ORM\JoinColumn(name="melding_uuid", referencedColumnName="uuid")
     */
    private $melding;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $aanmaakDatumtijd;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $clientIp;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $afzender;

    /**
     * @var string
     * @ORM\Column(type="string", length=25)
     */
    private $type;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $bericht;

    /**
     * Non mapped value, true if just created
     * @var boolean
     */
    private $isNew = false;

    /**
     * @var Handhaver
     * @ORM\ManyToOne(targetEntity="Handhaver")
     * @ORM\JoinColumn(name="handhaver_id", referencedColumnName="id", nullable=true)
     */
    private $handhaver;

    /**
     * @var number
     * @ORM\Column(type="integer", nullable=true)
     */
    private $beoordeling;

    /**
     * Init the Reactie object
     */
    public function __construct()
    {
        $this->aanmaakDatumtijd = new \DateTime();
    }

    /**
     * @return number
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Melding
     */
    public function getMelding()
    {
        return $this->melding;
    }

    /**
     * @return \DateTime
     */
    public function getAanmaakDatumtijd()
    {
        return $this->aanmaakDatumtijd;
    }

    /**
     * @return string
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * @return string
     */
    public function getAfzender()
    {
        return $this->afzender;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getBericht()
    {
        return $this->bericht;
    }

    /**
     * @return boolean
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * @return \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Handhaver
     */
    public function getHandhaver()
    {
        return $this->handhaver;
    }

    /**
     * @return number
     */
    public function getBeoordeling()
    {
        if ($this->beoordeling === null)
            return null;
        switch ($this->beoordeling)
        {
            case -2:
                return ':-@';
                break;
            case -1:
                return ':-(';
                break;
            case 0:
                return ':-|';
                break;
            case 1:
                return ':-)';
                break;
            case 2:
                return ':-D';
                break;
        }
        return ':-|';
    }

    /**
     * @param string $clientIp
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;
    }

    /**
     * @param Melding $melding
     */
    public function setMelding(Melding $melding = null)
    {
        if ($this->melding !== $melding)
            $this->melding = $melding;
        if ($melding->hasReactie($this) === false)
            $melding->addReactie($this);
    }

    /**
     * @param string $afzender
     */
    public function setAfzender($afzender)
    {
        $this->afzender = $afzender;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param string $bericht
     */
    public function setBericht($bericht)
    {
        $this->bericht = $bericht;
    }

    /**
     * Set isNew to true
     */
    public function setIsNew()
    {
        $this->isNew = true;
    }

    /**
     * @param Handhaver $handhaver
     */
    public function setHandhaver(Handhaver $handhaver = null)
    {
        $this->handhaver = $handhaver;
    }

    /**
     * @param string $beoordling
     * @return number
     */
    public function setBeoordeling($beoordeling = null)
    {
        if ($beoordeling === null) {
            $this->beoordeling = null;
            return;
        }
        switch ($beoordeling)
        {
            case ':-@':
                $this->beoordeling = -2;
                break;
            case ':-(':
                $this->beoordeling = -1;
                break;
            case ':-|':
                $this->beoordeling = 0;
                break;
            case ':-)':
                $this->beoordeling = 1;
                break;
            case ':-D':
                $this->beoordeling = 2;
                break;
        }
        return 0;
    }
}