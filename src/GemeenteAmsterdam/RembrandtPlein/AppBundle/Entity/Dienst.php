<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DienstRepository")
 * @ORM\Table(
 *  name="dienst"
 * )
 */
class Dienst
{
    /**
     * @var integer
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $start;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $eind;

    /**
     * @var Handhaver
     * @ORM\ManyToOne(targetEntity="Handhaver")
     * @ORM\JoinColumn(name="handhaver_id", nullable=true)
     */
    private $handhaver;

    /**
     * @return number
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return \DateTime
     */
    public function getEind()
    {
        return $this->eind;
    }

    /**
     * @return \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Handhaver
     */
    public function getHandhaver()
    {
        return $this->handhaver;
    }

    /**
     * @param \DateTime $start
     */
    public function setStart(\DateTime $start = null)
    {
        $this->start = $start;
    }

    /**
     * @param \DateTime $eind
     */
    public function setEind(\DateTime $eind = null)
    {
        $this->eind = $eind;
    }

    /**
     * @param Handhaver $handhaver
     */
    public function setHandhaver(Handhaver $handhaver = null)
    {
        $this->handhaver = $handhaver;
    }
}