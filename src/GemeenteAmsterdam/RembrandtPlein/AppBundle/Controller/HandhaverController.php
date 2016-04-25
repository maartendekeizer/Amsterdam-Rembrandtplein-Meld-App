<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Reactie;
use Symfony\Component\HttpFoundation\Request;
use GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Dienst;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HandhaverController extends Controller
{
    /**
     * @Route("/handhaver/{pageNumber}/{limit}",
     *  requirements={
     *      "pageNumber" = "\d+",
     *      "limit" = "\d+"
     *  }
     * )
     */
    public function indexAction($pageNumber = 0, $limit = 50)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');

        $meldingen = $meldingRepository->findAllWithPagination($limit, $pageNumber * $limit);

        $firstMelding = $meldingRepository->getMostRecent();

        return $this->render('RembrandtPleinAppBundle:Handhaver:index.html.twig', [
            'meldingen' => $meldingen,
            'firstMelding' => $firstMelding,
            'pageSize' => $limit,
            'pageNumber' => $pageNumber, // zero based
            'pageCount' => ceil(count($meldingen) / $limit),
            'activeDienst' => $dienstRepository->getActiveDienst(),
        ]);
    }

    /**
     * @Route("/handhaver/meldingen/lijst/{pageNumber}/{limit}")
     */
    public function lijstOverviewMeldingAction(Request $request, $pageNumber = 0, $limit = 50)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');

        $meldingen = $meldingRepository->findAllWithPagination($limit, $pageNumber * $limit);

        return $this->render('RembrandtPleinAppBundle:Handhaver:lijstOverviewMelding.html.twig', [
            'meldingen' => $meldingen,
            'pageSize' => $limit,
            'pageNumber' => $pageNumber, // zero based
            'pageCount' => ceil(count($meldingen) / $limit),
        ]);
    }

    /**
     * @Route("/handhaver/melding/{meldingUuid}")
     */
    public function detailMeldingAction(Request $request, $meldingUuid)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');

        $isNewReactieIds = [];
        if ($request->query->has('new') === true) {
            $isNewReactieIds[] = $request->query->getInt('new');
        }

        // get Melding object
        $melding = $meldingRepository->get($meldingUuid);
        if ($melding === null) {
            throw $this->createNotFoundException('Melding niet gevonden');
        }

        $activeDienst = $dienstRepository->getActiveDienst();

        if ($melding->isGelezen() === false && $activeDienst !== null && $activeDienst->getHandhaver() === $this->getUser()) {
            $melding->setGelezen();
            $this->getDoctrine()->getManager()->flush();

            $reactie = new Reactie();
            $reactie->setAfzender(Reactie::AFZENDER_HANDHAVER);
            $reactie->setHandhaver($this->getUser());
            $reactie->setBericht('Melding is gelezen door team op Rembrandtplein');
            $reactie->setClientIp($request->getClientIp());
            $reactie->setType(Reactie::TYPE_STATUS_GELEZEN);
            $reactie->isNew();
            $melding->addReactie($reactie);

            $this->getDoctrine()->getManager()->persist($reactie);
            $this->getDoctrine()->getManager()->flush();

            if ($melding->getMelderNotificaties() === true && $melding->getMelder()->hasNotificatieInformatie() === true) {
                $this->get('rembrandtplein.appbundle.services.sms')->sentSmsFromTemplate($melding->getMelder()->getMobielNummer(), 'RembrandtPleinAppBundle:sms:readMelding.txt.twig', ['melding' => $melding, 'reactie' => $reactie]);
            }

            $isNewReactieIds[] = $reactie->getId();
        }

        // mark a Reactie as new
        if (count($isNewReactieIds) > 0) {
            foreach ($melding->getReacties() as $reactie) {
                /* @var $reactie Reactie */
                if (in_array($reactie->getId(), $isNewReactieIds) === true) {
                    $reactie->setIsNew();
                }
            }
        }

        return $this->render('RembrandtPleinAppBundle:Handhaver:detailMelding.html.twig', [
            'melding' => $melding,
            'activeDienst' => $activeDienst,
            'reactieForm' => [
                'data' => [
                    'bericht' => '',
                    'actie' => ($melding->getReactieVanMelderToestaan() ? 'reactie-toestaan' : ''),
                ],
                'errors' => [],
                'submitted' => true
            ],
            'metaForm' => [
                'data' => [
                    'categorie' => $melding->getCategorie(),
                    'coordinaten' => $melding->getCoordinaten(),
                    'adres' => $melding->getAdres(),
                    'locatie' => $melding->getLocatie(),
                ],
                'errors' => [],
                'submitted' => false
            ],
        ]);
    }

    /**
     * @Route("/handhaver/qwerty/melding/{meldingUuid}/{secret}")
     */
    public function legacyDetailMeldingAction(Request $request, $meldingUuid)
    {
        // OMG that is an old url!
        return $this->redirectToRoute('gemeenteamsterdam_rembrandtplein_app_handhaver_detailmelding', ['meldingUuid' => $meldingUuid]);
    }

    /**
     * @Route("/handhaver/melding/{meldingUuid}/reacties")
     */
    public function reactielijstMeldingAction(Request $request, $meldingUuid)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');

        // get Melding object
        $melding = $meldingRepository->get($meldingUuid);
        if ($melding === null) {
            throw $this->createNotFoundException('Melding niet gevonden');
        }

        $sinceReactieId = $request->query->getInt('sinceReactieId');

        $reacties = $melding->getReacties();
        $reacties = array_filter($reacties, function (Reactie $reactie) use ($sinceReactieId) {
            return $reactie->getId() > $sinceReactieId;
        });

        // create response
        return $this->render('RembrandtPleinAppBundle:Handhaver:reactielijstMelding.html.twig', [
            'melding' => $melding,
            'reacties' => $reacties, // is without the orginal message
        ]);
    }

    /**
     * @Route("/handhaver/melding/{meldingUuid}/reageer")
     */
    public function addReactieAction(Request $request, $meldingUuid)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');
        /* @var $validator \Symfony\Component\Validator\Validator\ValidatorInterface */
        $validator = $this->get('validator');

        // get melding object
        $melding = $meldingRepository->get($meldingUuid);
        if ($melding === null) {
            throw $this->createNotFoundException('Melding niet gevonden');
        }

        // actieve dienst ophalen
        $activeDienst = $dienstRepository->getActiveDienst();

        // build form
        $form = [
            'bericht' => $request->request->get('bericht', ''),
            'actie' => $request->request->get('actie', ''),
        ];

        // validate form
        $errors = new ConstraintViolationList();
        $errors->addAll($validator->validate($form['bericht'], new Assert\Length(['min' => 0, 'max' => 500])));

        // handle submit
        if ($request->getMethod() === 'POST' && count($errors) === 0) {
            // handle reactie toestaan change (only if changed)
            if (($form['actie'] === 'reactie-toestaan') !== $melding->getReactieVanMelderToestaan()) {
                $melding->setReactieVanMelderToestaan($form['actie'] === 'reactie-toestaan');

                // add system reactie if activated
                if ($form['actie'] === 'reactie-toestaan') {
                    $reactie = new Reactie();
                    $reactie->setAfzender(Reactie::AFZENDER_HANDHAVER);
                    $reactie->setBericht('Reactie gevraagd door handhaver');
                    $reactie->setHandhaver($this->getUser());
                    $reactie->setType(Reactie::TYPE_STATUS_REACTIE_TOESTAAN);
                    $melding->addReactie($reactie);
                    $this->getDoctrine()->getManager()->persist($reactie);
                }
            }

            // add reactie als bericht niet leeg is
            if ($form['bericht'] !== '') {
                // nieuwe reactie
                $reactie = new Reactie();
                $reactie->setAfzender(Reactie::AFZENDER_HANDHAVER);
                $reactie->setBericht($request->request->get('bericht', ''));
                $reactie->setClientIp($request->getClientIp());
                $reactie->setType(Reactie::TYPE_BERICHT);
                $reactie->setHandhaver($this->getUser());
                $melding->addReactie($reactie);
                $this->getDoctrine()->getManager()->persist($reactie);

                // send notification
                if ($melding->getMelderNotificaties() === true && $melding->getMelder()->hasNotificatieInformatie() === true) {
                    $this->get('rembrandtplein.appbundle.services.sms')->sentSmsFromTemplate($melding->getMelder()->getMobielNummer(), 'RembrandtPleinAppBundle:sms:updateMelding.txt.twig', ['melding' => $melding, 'reactie' => $reactie]);
                }
            }

            // save database
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gemeenteamsterdam_rembrandtplein_app_handhaver_detailmelding', ['meldingUuid' => $melding->getUuid()]);
        }

        return $this->render('RembrandtPleinAppBundle:Handhaver:detail.html.twig', [
            'melding' => $melding,
            'actieveDienst' => $actieveDienst,
            'reactieForm' => [
                'data' => $form,
                'errors' => $errors,
                'submitted' => true
            ],
            'metadataForm' => [
                'data' => ['categorie' => $melding->getCategorie(), 'locatie' => $melding->getLocatie(), 'coordinaten' => $melding->getCoordinaten()],
                'errors' => [],
                'submitted' => false,
            ],
        ]);
    }

    /**
     * @Route("/handhaver/melding/{meldingUuid}/metadata")
     */
    public function setMetaDataAction(Request $request, $meldingUuid)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');
        /* @var $validator \Symfony\Component\Validator\Validator\ValidatorInterface */
        $validator = $this->get('validator');

        // get melding object
        $melding = $meldingRepository->get($meldingUuid);
        if ($melding === null) {
            throw $this->createNotFoundException('Melding niet gevonden');
        }

        // actieve dienst ophalen
        $activeDienst = $dienstRepository->getActiveDienst();

        // build form
        $form = [
            'coordinaten' => $request->request->get('coordinaten'),
            'locatie' => $request->request->get('locatie'),
            'adres' => $request->request->get('adres'),
            'categorie' => $request->request->get('categorie'),
        ];

        // validate form
        $errors = new ConstraintViolationList();
        $errors->addAll($validator->validate($form['categorie'], new Assert\Length(['min' => 0, 'max' => 255])));
        $errors->addAll($validator->validate($form['locatie'], new Assert\Length(['min' => 0, 'max' => 255])));
        $errors->addAll($validator->validate($form['adres'], new Assert\Length(['min' => 0, 'max' => 255])));
        $errors->addAll($validator->validate($form['coordinaten'], new Assert\Length(['min' => 0, 'max' => 255])));

        // handle submit
        if ($request->getMethod() === 'POST' && count($errors) === 0) {
            $melding->setCategorie($form['categorie']);
            $melding->setCoordinaten($form['coordinaten']);
            $melding->setAdres($form['adres']);
            $melding->setLocatie($form['locatie']);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gemeenteamsterdam_rembrandtplein_app_handhaver_detailmelding', ['meldingUuid' => $melding->getUuid()]);
        }

        return $this->render('RembrandtPleinAppBundle:Handhaver:detail.html.twig', [
            'melding' => $melding,
            'actieveDienst' => $actieveDienst,
            'reactieForm' => [
                'data' => [
                    'bericht' => '',
                    'actie' => ($melding->getReactieVanMelderToestaan() ? 'reactie-toestaan' : ''),
                ],
                'errors' => [],
                'submitted' => false
            ],
            'metadataForm' => [
                'data' => $form,
                'errors' => $errors,
                'submitted' => $request->getMethod() === 'POST',
            ],
        ]);
    }

    /**
     * @Route("/handhaver/dienst/start")
     */
    public function startDienstAction(Request $request)
    {
        /* @var $handhaverRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\HandhaverRepository */
        $handhaverRepository = $this->get('rembrandtplein.appbundle.repositories.handhaver');
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');

        $handhaverId = $request->request->get('handhaver_id');

        $handhaver = $handhaverRepository->find($handhaverId);
        if ($handhaver === null)
            throw $this->createNotFoundException('Handhaver not known');

        $dienstRepository->stopAllDiensten();

        $dienst = new Dienst();
        $dienst->setStart(new \DateTime());
        $dienst->setHandhaver($handhaver);
        $dienst->setEind(null);

        $this->getDoctrine()->getManager()->persist($dienst);
        $this->getDoctrine()->getManager()->flush($dienst);

        if ($request->request->has('_target_path')) {
            return $this->redirect($request->request->get('_target_path'));
        }

        return $this->redirectToRoute('gemeenteamsterdam_rembrandtplein_app_handhaver_index', [], Response::HTTP_TEMPORARY_REDIRECT);
    }

    /**
     * @Route("/handhaver/dienst/eind/{id}")
     * @Method("POST")
     */
    public function stopDienst(Request $request, $id)
    {
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\HandhaverRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');

        $dienst = $dienstRepository->find($id);
        if ($dienst === null)
            throw $this->createNotFoundException('Dienst not found');

        $dienst->setEind(new \DateTime());

        $this->getDoctrine()->getManager()->flush($dienst);

        if ($request->request->has('_target_path')) {
            return $this->redirect($request->request->get('_target_path'));
        }

        return $this->redirectToRoute('gemeenteamsterdam_rembrandtplein_app_handhaver_index', [], Response::HTTP_TEMPORARY_REDIRECT);
    }

    /**
     * @Route("/handhaver/login")
     */
    public function loginAction(Request $request)
    {
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');
        /* @var $handhaverRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\HandhaverRepository */
        $handhaverRepository = $this->get('rembrandtplein.appbundle.repositories.handhaver');

        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('RembrandtPleinAppBundle:Handhaver:login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'activeDienst' => $dienstRepository->getActiveDienst(),
            'handhavers' => $handhaverRepository->findAllActive()
        ]);
    }

    /**
     * @Route("/handhaver/login_check")
     */
    public function loginCheckAction()
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }

    /**
     * @Route("/handhaver/logout")
     */
    public function logoutAction()
    {
    }

    /**
     * @Route("/handhaver/export")
     */
    public function exportAction(Request $request)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');
        /* @var $validator \Symfony\Component\Validator\Validator\ValidatorInterface */
        $validator = $this->get('validator');

        // actieve dienst ophalen
        $activeDienst = $dienstRepository->getActiveDienst();

        // build form
        $form = [
            'startdate' => $request->request->get('startdate', date('Y-m-d', time() - (7 * 24 * 60 * 60))),
            'starttime' => $request->request->get('starttime', date('H:i:s')),
            'enddate' => $request->request->get('enddate', date('Y-m-d')),
            'endtime' => $request->request->get('endtime', date('H:i:s')),
        ];

        // validate form
        $errors = new ConstraintViolationList();
        $errors->addAll($validator->validate($form['startdate'], new Assert\Date()));
        $errors->addAll($validator->validate($form['starttime'], new Assert\Time()));
        $errors->addAll($validator->validate($form['enddate'], new Assert\Date()));
        $errors->addAll($validator->validate($form['endtime'], new Assert\Time()));

        // handle submit
        if ($request->getMethod() !== 'POST' || count($errors) !== 0) {
            return $this->render('RembrandtPleinAppBundle:Handhaver:export.html.twig', [
                'form' => [
                    'data' => $form,
                    'errors' => $errors,
                    'submitted' => $request->getMethod() === 'POST'
                ],
                'activeDienst' => $activeDienst,
            ]);
        }

        // selec records
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', $form['startdate'] . ' ' . $form['starttime']);
        $eind = \DateTime::createFromFormat('Y-m-d H:i:s', $form['enddate'] . ' ' . $form['endtime']);
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        $records = $meldingRepository->findBetween($start, $eind);


        // create Excel!
        /* @var $objPHPExcel \PHPExcel */
        $objPHPExcel = $this->get('phpexcel')->createPHPExcelObject();

        // set properties
        $objPHPExcel->getProperties()
            ->setTitle('Rapportage meldingen Vliegende Brigade app')
            ->setCreator('Vliegende Brigade app');

        // set active sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();

        // build basic header
        $sheet->getCellByColumnAndRow(0, 1)->setValue('UUID')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(1, 1)->setValue('URL')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(2, 1)->setValue('Datum/tijd gestart')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(3, 1)->setValue('Datum/tijd gelezen')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(4, 1)->setValue('Aantal reacties')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(5, 1)->setValue('Notificaties')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(6, 1)->setValue('Categorie')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(7, 1)->setValue('Locatie')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(8, 1)->setValue('Adres')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(9, 1)->setValue('Coordinaten')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(10, 1)->setValue('Type')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(11, 1)->setValue('Datum/tijd bericht')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(12, 1)->setValue('Tijd sinds melding')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(13, 1)->setValue('Afzender')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(14, 1)->setValue('Handhaver')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getCellByColumnAndRow(15, 1)->setValue('Bericht')->getStyle()->applyFromArray(['font' => ['bold' => true]]);
        $sheet->freezePaneByColumnAndRow(1, 2);

        // build records
        $i = 2;
        foreach ($records as $record) {
            /* @var $record \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Melding */
            $textColor = '000000';
            $meldingRow = $i;
            foreach ($record->getAllReacties() as $j => $reactie) {
                $sheet->getCellByColumnAndRow(0, $i)->setValue($record->getUuid());
                $sheet->getCellByColumnAndRow(0, $i)->getStyle()->applyFromArray(['font' => ['color' => ['rgb' => $textColor]]]);

                $sheet->getCellByColumnAndRow(1, $i)->setValue($this->generateUrl('gemeenteamsterdam_rembrandtplein_app_handhaver_detailmelding', ['meldingUuid' => $record->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL))->getStyle()->applyFromArray(['font' => ['color' => ['rgb' => $textColor]]]);

                $sheet->getCellByColumnAndRow(2, $i)->setValue(\PHPExcel_Shared_Date::PHPToExcel($record->getAanmaakDatumtijd()))->getStyle()->getNumberFormat()->setFormatCode('ddd dd-mm-yy hh:mm:ss');
                $sheet->getCellByColumnAndRow(2, $i)->getStyle()->applyFromArray(['font' => ['color' => ['rgb' => $textColor]]]);

                if ($record->getDatumtijdGelezen() !== null) {
                    $sheet->getCellByColumnAndRow(3, $i)->setValue(\PHPExcel_Shared_Date::PHPToExcel($record->getDatumtijdGelezen()))->getStyle()->getNumberFormat()->setFormatCode('dd-mm-yy hh:mm:ss');
                }
                $sheet->getCellByColumnAndRow(3, $i)->getStyle()->applyFromArray(['font' => ['color' => ['rgb' => $textColor]]]);

                $sheet->getCellByColumnAndRow(4, $i)->setValue($record->getAantalBerichtReacties())->getStyle()->applyFromArray(['font' => ['color' => ['rgb' => $textColor]]]);

                $sheet->getCellByColumnAndRow(5, $i)->setValue($record->getMelderNotificaties() === true ? 'aan' : 'uit')->getStyle()->applyFromArray(['font' => ['color' => ['rgb' => $textColor]]]);

                $sheet->getCellByColumnAndRow(6, $i)->setValue($record->getCategorie())->getStyle()->applyFromArray(['font' => ['color' => ['rgb' => $textColor]]]);

                $sheet->getCellByColumnAndRow(7, $i)->setValue($record->getLocatie())->getStyle()->applyFromArray(['font' => ['color' => ['rgb' => $textColor]]]);

                $sheet->getCellByColumnAndRow(8, $i)->setValue($record->getAdres())->getStyle()->applyFromArray(['font' => ['color' => ['rgb' => $textColor]]]);

                $sheet->getCellByColumnAndRow(9, $i)->setValue($record->getCoordinaten())->getStyle()->applyFromArray(['font' => ['color' => ['rgb' => $textColor]]]);

                $sheet->getCellByColumnAndRow(10, $i)->setValue($j === 0 ? 'Melding' : 'Reactie');

                $sheet->getCellByColumnAndRow(11, $i)->setValue(\PHPExcel_Shared_Date::PHPToExcel($reactie->getAanmaakDatumtijd()))->getStyle()->getNumberFormat()->setFormatCode('ddd hh:mm:ss');

                $sheet->getCellByColumnAndRow(12, $i)->setValue('=L' . $i . '-' . 'L' . $meldingRow);
                $sheet->getCellByColumnAndRow(12, $i)->getStyle()->getNumberFormat()->setFormatCode('hh:mm:ss');

                $sheet->getCellByColumnAndRow(13, $i)->setValue($reactie->getAfzender());

                $sheet->getCellByColumnAndRow(14, $i)->setValue($reactie->getHandhaver() !== null ? $reactie->getHandhaver()->getNaam() : '');

                $sheet->getCellByColumnAndRow(15, $i)->setValue($reactie->getBericht());

                $i ++;
                $textColor = 'CCCCCC';
            }
            $i ++; // add blank record between meldingen
        }

        // set width per column
        $sheet->getColumnDimension('C')->setWidth(19);
        $sheet->getColumnDimension('D')->setWidth(17);
        $sheet->getColumnDimension('L')->setWidth(17);
        $sheet->getColumnDimension('J')->setVisible(false);


        $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'export-' . date('YmdHis') . '.xlsx');
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        return $response;

    }
}