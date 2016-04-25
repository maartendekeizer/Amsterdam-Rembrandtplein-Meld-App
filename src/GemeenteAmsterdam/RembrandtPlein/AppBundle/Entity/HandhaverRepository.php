<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class HandhaverRepository extends EntityRepository
{
    /**
     * @return Handhaver[]
     */
    public function findAllActive()
    {
        $qb = $this->createQueryBuilder('handhaver');
        $qb->select('handhaver');
        $qb->andWhere('handhaver.isActive = :isActive');
        $qb->setParameter('isActive', true);
        $qb->addOrderBy('handhaver.naam', 'ASC');
        $qb->addOrderBy('handhaver.id', 'ASC');

        return $qb->getQuery()->execute();
    }
}