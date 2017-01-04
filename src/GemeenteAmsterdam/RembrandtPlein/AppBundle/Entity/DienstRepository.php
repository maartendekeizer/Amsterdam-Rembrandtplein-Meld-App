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

class DienstRepository extends EntityRepository
{
    /**
     * @return NULL|Dienst
     */
    public function getActiveDienst()
    {
        $qb = $this->createQueryBuilder('dienst');
        $qb->select('dienst');
        $qb->addSelect('handhaver');
        $qb->join('dienst.handhaver', 'handhaver');
        $qb->andWhere('dienst.start IS NOT NULL');
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->isNull('dienst.eind'),
                $qb->expr()->gt('dienst.eind', ':now')
            )
        );
        $qb->setParameter('now', new \DateTime());
        $qb->addOrderBy('dienst.start', 'DESC');
        $qb->addOrderBy('dienst.id', 'DESC');

        $diensten = $qb->getQuery()->execute();

        if (count($diensten) === 0)
            return null;

        return reset($diensten);
    }

    /**
     * Stopt alle actieve diensten
     */
    public function stopAllDiensten()
    {
        $query = $this->_em->createQuery('UPDATE ' . $this->_entityName . ' dienst SET dienst.eind = :eind WHERE dienst.eind IS NULL');
        $query->setParameter('eind', new \DateTime());
        $query->execute();
    }
}