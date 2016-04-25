<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class MeldingRepository extends EntityRepository
{
    /**
     * @param string $uuid
     * @return Melding
     */
    public function get($uuid)
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    /**
     * @param Melder $melder
     * @param int $numberOfDays
     * @return Melding[]
     */
    public function recent($melder, $numberOfDays)
    {
        $start = new \DateTime();
        $end = clone $start;
        $end->sub(new \DateInterval('P' . $numberOfDays . 'D'));

        $qb = $this->createQueryBuilder('melding')
            ->select('melding')
            ->addSelect('reactie')
            ->addSelect('melder')
            ->andWhere('melding.melder = :melder')
            ->setParameter('melder', $melder)
            ->andWhere('melding.aanmaakDatumtijd BETWEEN :end AND :start')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->join('melding.melder', 'melder')
            ->join('melding.reacties', 'reactie')
            ->orderBy('melding.gewijzigdDatumtijd', 'DESC');

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\ORM\EntityRepository::findAll()
     * @return Melding[]
     */
    public function findAll()
    {
        return $this->findBy([], ['aanmaakDatumtijd' => 'DESC']);
    }

    /**
     * @param number $limit
     * @param number $offset
     * @return \Doctrine\ORM\Tools\Pagination\Paginator|Melding[]
     */
    public function findAllWithPagination($limit, $offset)
    {
        $qb = $this->createQueryBuilder('melding');
        $qb->join('melding.melder', 'melder')->addSelect('melder');
        $qb->orderBy('melding.gewijzigdDatumtijd', 'DESC');
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        $paginator = new Paginator($qb->getQuery());
        return $paginator;
    }

    public function getMostRecent()
    {
        $qb = $this->createQueryBuilder('melding');
        $qb->join('melding.melder', 'melder')->addSelect('melder');
        $qb->orderBy('melding.gewijzigdDatumtijd', 'DESC');
        $qb->setMaxResults(1);
        $qb->setFirstResult(0);

        $results = $qb->getQuery()->execute();
        if (count($results) === 0)
            return null;
        return reset($results);
    }

    public function findBetween($start, $eind)
    {
        $qb = $this->createQueryBuilder('melding');
        $qb->join('melding.melder', 'melder')->addSelect('melder');
        $qb->andWhere('melding.aanmaakDatumtijd BETWEEN :start AND :eind');
        $qb->setParameter('start', $start);
        $qb->setParameter('eind', $eind);
        $qb->orderBy('melding.aanmaakDatumtijd', 'ASC');

        return $qb->getQuery()->execute();
    }
}