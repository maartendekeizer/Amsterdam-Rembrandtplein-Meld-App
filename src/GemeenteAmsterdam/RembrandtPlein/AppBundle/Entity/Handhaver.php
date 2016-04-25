<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="HandhaverRepository")
 * @ORM\Table(
 *  name="handhaver"
 * )
 */
class Handhaver implements UserInterface, \Serializable
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
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $naam;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $avatar;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $password;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var string
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    private $telefoon;

    /**
     * Init Handhaver object
     */
    public function __construct()
    {
        $this->isActive = true;
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
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getUsername()
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getPassword()
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getTelefoon()
    {
        return $this->telefoon;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getRoles()
     */
    public function getRoles()
    {
        return ['ROLE_HANDHAVER'];
    }

    /**
     * @param string $naam
     */
    public function setNaam($naam)
    {
        $this->naam = $naam;
    }

    /**
     * @param string $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param string $telefoon
     */
    public function setTelefoon($telefoon)
    {
        $this->telefoon = $telefoon;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::eraseCredentials()
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getSalt()
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        return serialize([
            'id' => $this->id,
            'naam' => $this->naam,
            'avatar' => $this->avatar,
            'username' => $this->username,
            'password' => $this->password,
            'telefoon' => $this->telefoon
        ]);
    }

    /**
     * {@inheritDoc}
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        $serialized = unserialize($serialized);
        $this->id = $serialized['id'];
        $this->naam  = $serialized['naam'];
        $this->avatar = $serialized['avatar'];
        $this->username = $serialized['username'];
        $this->password = $serialized['password'];
        $this->telefoon = $serialized['telefoon'];
    }
}