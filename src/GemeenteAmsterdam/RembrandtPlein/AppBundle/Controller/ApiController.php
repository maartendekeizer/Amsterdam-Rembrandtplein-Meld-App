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

namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @Route("/api/1.0")
 */
class ApiController extends Controller
{
    /**
     * @Route("/meldingen")
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  description="Maak een lijst met meldingen",
     *  filters={
     *      {"name"="dateStart", "required"=false, "dataType"="string", "description"="Date as yyyy-mm-dd or yyyy-mm-ddThh:ii:ss"},
     *      {"name"="dateEnd", "required"=false, "dataType"="string", "description"="Date as yyyy-mm-dd or yyyy-mm-ddThh:ii:ss"},
     *      {"name"="categorie", "required"=false, "dataType"="string", "description"="Search for meldingen with a specific categorie"},
     *      {"name"="locatie", "required"=false, "dataType"="string", "description"="Search for meldingen with a specific locatie"},
     *      {"name"="pageSize", "required"=false, "dataType"="integer", "description"="Number of items on a page, max 100"},
     *      {"name"="pageNumber", "required"=false, "dataType"="integer", "description"="Page number, zero based"},
     *  }
     * )
     */
    public function indexAction(Request $request)
    {
        // pagination
        $pageSize = $request->query->getInt('pageSize', 10);
        $pageSize = ($pageSize > 100) ? $pageSize = 100 : $pageSize;
        $pageNumber = $request->query->getInt('pageNumber', 0);

        // filters
        $dateStart = null;
        if ($request->query->has('dateStart')) {
            $dateStart = \DateTime::createFromFormat('Y-m-d\\TH:i:s', $request->query->get('dateStart'));
        }
        $dateEnd = null;
        if ($request->query->has('dateEnd')) {
            $dateEnd = \DateTime::createFromFormat('Y-m-d\\TH:i:s', $request->query->get('dateEnd'));
        }

        // build query
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('melding');
        $qb->addSelect('reactie');
        $qb->from('RembrandtPleinAppBundle:Melding', 'melding');
        $qb->leftJoin('melding.reacties', 'reactie');
        if (($dateStart instanceof \DateTime) === true) {
            $qb->andWhere('melding.aanmaakDatumtijd > :dateStart');
            $qb->setParameter('dateStart', $dateStart);
        }
        if (($dateEnd instanceof \DateTime) === true) {
            $qb->andWhere('melding.aanmaakDatumtijd < :dateEnd');
            $qb->setParameter('dateEnd', $dateEnd);
        }
        if ($request->query->get('categorie', '') !== '') {
            $qb->andWhere('melding.categorie = :categorie');
            $qb->setParameter('categorie', $request->query->get('categorie'));
        }
        if ($request->query->get('locatie', '') !== '') {
            $qb->andWhere('melding.locatie LIKE :locatie');
            $qb->setParameter('locatie', '%' . $request->query->get('locatie') . '%');
        }
        if ($request->query->get('adres', '') !== '') {
            $qb->andWhere('melding.adres LIKE :adres');
            $qb->setParameter('adres', '%' . $request->query->get('adres'));
        }
        $qb->orderBy('melding.aanmaakDatumtijd', 'DESC');
        $qb->setMaxResults($pageSize);
        $qb->setFirstResult($pageSize * $pageNumber);
        // set paginators
        $p = new Paginator($qb->getQuery());
        // build output
        $response = ['meldingen' => []];
        $meldingen = $qb->getQuery()->execute();
        foreach ($p as $melding) {
            $meldingObject = [
                'uuid' => $melding->getUuid(),
                'aanmaakDatumtijd' => $melding->getAanmaakDatumtijd()->format('c'),
                'isGelezen' => $melding->isGelezen(),
                'title' => $melding->getFirstReactie()->getBericht(),
                'categorie' => $melding->getCategorie(),
                'locatie' => $melding->getLocatie(),
                'adres' => $melding->getAdres(),
                'coordinaten' => $melding->getCoordinaten(),
                'reacties' => []
            ];
            foreach ($melding->getReacties() as $reactie) {
                $meldingObject['reacties'][] = [
                    'aanmaakDatumtijd' => $reactie->getAanmaakDatumtijd()->format('c'),
                    'afzender' => $reactie->getAfzender(),
                    'type' => $reactie->getType(),
                    'bericht' => $reactie->getBericht(),
                ];
            }
            $response['meldingen'][] = $meldingObject;
        }
        $response['total'] = count($p);
        $response['pageSize'] = $pageSize;
        $response['pageNumber'] = $pageNumber;
        $response['numberOfPages'] = ceil(count($p) / $pageSize);
        // return the output as JSON
        return new JsonResponse($response);
    }
}