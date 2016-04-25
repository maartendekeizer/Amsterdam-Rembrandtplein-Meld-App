<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Controller;

use GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\CookieToken;
use GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Melding;
use GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\Reactie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;

class MelderController extends Controller
{
    /**
     * @Route("/melder")
     */
    public function indexAction(Request $request, CookieToken $cookieToken)
    {
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');

        // check if we are in service
        $activeDienst = $dienstRepository->getActiveDienst();

        return $this->render('RembrandtPleinAppBundle:Melder:index.html.twig', [
            'cookieToken' => $cookieToken,
            'activeDienst' => $activeDienst
        ]);
    }

    /**
     * @Route("/melder/meldingen")
     */
    public function overviewAction(Request $request, CookieToken $cookieToken)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');

        // get active dienst
        $activeDienst = $dienstRepository->getActiveDienst();

        // get recente meldingen
        $recenteMeldingen = $meldingRepository->recent($cookieToken->getMelder(), 365);

        return $this->render('RembrandtPleinAppBundle:Melder:overview.html.twig', [
            'cookieToken' => $cookieToken,
            'totaalAantalMeldingen' => count($cookieToken->getMelder()->getMeldingen()),
            'recenteMeldingen' => $recenteMeldingen,
            'activeDienst' => $activeDienst
        ]);
    }

    /**
     * @Route("/melder/meldingen/lijst")
     */
    public function lijstOverviewMeldingAction(Request $request, CookieToken $cookieToken)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');

        // get recente meldingen
        $recenteMeldingen = $meldingRepository->recent($cookieToken->getMelder(), 365);

        return $this->render('RembrandtPleinAppBundle:Melder:lijstOverviewMelding.html.twig', [
            'cookieToken' => $cookieToken,
            'recenteMeldingen' => $recenteMeldingen,
        ]);
    }

    /**
     * @Route("/melder/niet-aanwezig")
     */
    public function notInServiceAction(Request $request, CookieToken $cookieToken)
    {
        return $this->render('RembrandtPleinAppBundle:Melder:notInService.html.twig', [
            'cookieToken' => $cookieToken
        ]);
    }

    /**
     * @Route("/melder/hoe-werkt-het")
     */
    public function aboutAction(Request $request, CookieToken $cookieToken)
    {
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');

        // get active dienst
        $activeDienst = $dienstRepository->getActiveDienst();

        return $this->render('RembrandtPleinAppBundle:Melder:about.html.twig', [
            'cookieToken' => $cookieToken,
            'activeDienst' => $activeDienst
        ]);
    }

    /**
     * @Route("/melder/melding")
     */
    public function createMeldingAction(Request $request, CookieToken $cookieToken)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');
        /* @var $secretGenerator \GemeenteAmsterdam\RembrandtPlein\AppBundle\Utils\SecretGenerator */
        $secretGenerator = $this->get('rembrandtplein.appbundle.utils.secretgenerator');
        /* @var $validator \Symfony\Component\Validator\Validator\ValidatorInterface */
        $validator = $this->get('validator');

        // check if we are in service
        $activeDienst = $dienstRepository->getActiveDienst();
        if ($activeDienst === null) {
            return $this->redirectToRoute('gemeenteamsterdam_rembrandtplein_app_melder_notinservice', [], Response::HTTP_TEMPORARY_REDIRECT);
        }

        // get form data
        $form = [
            'bericht' => $request->request->get('bericht', ''),
            'captcha' => strtolower($request->request->get('email', ''))
        ];

        // validate form
        $errors = new ConstraintViolationList();
        $errors->addAll($validator->validate($form['bericht'], new Assert\NotBlank()));
        $errors->addAll($validator->validate($form['bericht'], new Assert\Length(['min' => 10, 'max' => 300])));
        $errors->addAll($validator->validate($form['captcha'], new Assert\Choice(['choices' => ['rood', 'red', 'root', '#FF0000', 'FF0000']])));

        // handle submit
        if ($request->getMethod() === 'POST' && count($errors) === 0) {

            // create a new Melding object
            $melding = new Melding();
            $melding->setSecret($secretGenerator->generate()); // generate a secret here!
            $melding->setInitieleBehandelaar($activeDienst->getHandhaver());
            $cookieToken->getMelder()->addMelding($melding);
            $this->getDoctrine()->getManager()->persist($melding);

            // create a new Reactie object
            $reactie = new Reactie();
            $reactie->setAfzender(Reactie::AFZENDER_MELDER);
            $reactie->setBericht($form['bericht']);
            $reactie->setClientIp($request->getClientIp());
            $reactie->setType(Reactie::TYPE_BERICHT);
            $melding->addReactie($reactie);
            $this->getDoctrine()->getManager()->persist($reactie);

            // save to database
            $this->getDoctrine()->getManager()->flush();

            // sent the sms to Handhaver
            if ($activeDienst->getHandhaver()->getTelefoon() !== null && $activeDienst->getHandhaver()->getTelefoon() !== '') {
                $this->get('rembrandtplein.appbundle.services.sms')->sentSmsFromTemplate($activeDienst->getHandhaver()->getTelefoon(), 'RembrandtPleinAppBundle:sms:newMelding.txt.twig', ['melding' => $melding, 'reactie' => $reactie]);
            }

            // administrate the sending of the sms
            $melding->setVerstuurd(true);
            $reactie = new Reactie();
            $reactie->setAfzender(Reactie::AFZENDER_SYSTEEM);
            $reactie->setBericht('Melding is ontvangen');
            $reactie->setClientIp($request->getClientIp());
            $reactie->setType(Reactie::TYPE_STATUS_VERSTUURD);
            $melding->addReactie($reactie);
            $this->getDoctrine()->getManager()->persist($reactie);

            // save to database
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gemeenteamsterdam_rembrandtplein_app_melder_detailmelding', ['meldingUuid' => $melding->getUuid(), 'secret' => $melding->getSecret()]);
        }

        return $this->render('RembrandtPleinAppBundle:Melder:createMelding.html.twig', [
            'form' => [
                'data' => $form,
                'errors' => $errors,
                'submitted' => ($request->getMethod() === 'POST')
            ],
            'activeDienst' => $activeDienst,
            'cookieToken' => $cookieToken
        ]);
    }

    /**
     * @Route("/melder/melding/{meldingUuid}/{secret}")
     */
    public function detailMeldingAction(Request $request, CookieToken $cookieToken, $meldingUuid, $secret)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');
        /* @var $validator \Symfony\Component\Validator\Validator\ValidatorInterface */
        $validator = $this->get('validator');

        // get active dienst
        $activeDienst = $dienstRepository->getActiveDienst();

        // get Melding object
        $melding = $meldingRepository->get($meldingUuid);
        if ($melding === null) {
            throw $this->createNotFoundException('Melding niet gevonden');
        }
        if ($melding->getSecret() !== $secret) {
            throw $this->createNotFoundException('URL is onjuist');
        }

        // check Melder
        $melderIsSame = $melding->getMelder() === $cookieToken->getMelder();

        // sms form
        $activateSmsForm = [
            'actief' => $melding->getMelderNotificaties(),
            'mobielnummer' => $melding->getMelder()->getMobielNummer()
        ];

        // bericht form
        $berichtForm = [
            'bericht' => '',
            't0' => time() . '|' . ($melding->getReactieVanMelderToestaan() ? 'y' : 'n'),
            't1' => md5($this->container->getParameter('secret') . '|' . time() . '|' . ($melding->getReactieVanMelderToestaan() ? 'y' : 'n'))
        ];

        // validate sms form
        $activateSmsErrors = new ConstraintViolationList();
        $activateSmsErrors->addAll($validator->validate($activateSmsForm['mobielnummer'], new Assert\Range(['min' => 600000000, 'max' => 699999999, 'minMessage' => 'Ongeldig telefoonnummer, moet starten met 06', 'maxMessage' => 'Ongeldig telefoonnummer, moet starten met 06'])));
        if ($melding->getMelderNotificaties() === true) {
            $activateSmsErrors->addAll($validator->validate($activateSmsForm['mobielnummer'], new Assert\NotBlank()));
        }

        // set isNew
        if ($request->query->has('new') === true) {
            foreach ($melding->getReacties() as $reactie) {
                /* @var $reactie Reactie */
                if ($request->query->getInt('new') === $reactie->getId()) {
                    $reactie->setIsNew();
                }
            }
        }

        // create response
        return $this->render('RembrandtPleinAppBundle:Melder:detailMelding.html.twig', [
            'melding' => $melding,
            'melderIsSame' => $melderIsSame,
            'activeDienst' => $activeDienst,
            'activateSmsForm' => [
                'data' => $activateSmsForm,
                'errors' => $activateSmsErrors,
                'submitted' => false
            ],
            'berichtForm' => [
                'data' => $berichtForm,
                'errors' => [],
                'submitted' => false
            ],
        ]);
    }

    /**
     * @Route("/melder/melding/{meldingUuid}/{secret}/activeer-sms-updates")
     */
    public function activateSmsAction(Request $request, CookieToken $cookieToken, $meldingUuid, $secret)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');
        /* @var $validator \Symfony\Component\Validator\Validator\ValidatorInterface */
        $validator = $this->get('validator');

        // get Melding object
        $melding = $meldingRepository->get($meldingUuid);
        if ($melding === null) {
            throw $this->createNotFoundException('Melding niet gevonden');
        }
        if ($melding->getSecret() !== $secret) {
            throw $this->createNotFoundException('URL is onjuist');
        }

        // get active dienst
        $activeDienst = $dienstRepository->getActiveDienst();

        // check Melder
        $melderIsSame = $melding->getMelder() === $cookieToken->getMelder();

        // check if this is original melder, SMS can only activated by original melder
        if ($melderIsSame === false) {
            throw $this->createAccessDeniedException('Not the orginal Melder');
        }

        // sms form
        $activateSmsForm = [
            'actief' => ($request->request->get('actief', '0') == '1'),
            'mobielnummer' => $this->formatMobielNummer($request->request->get('mobielnummer'))
        ];

        // validate sms form
        $activateSmsErrors = new ConstraintViolationList();
        $activateSmsErrors->addAll($validator->validate($activateSmsForm['mobielnummer'], new Assert\Range(['min' => 600000000, 'max' => 699999999, 'minMessage' => 'Ongeldig telefoonnummer, moet starten met 06', 'maxMessage' => 'Ongeldig telefoonnummer, moet starten met 06'])));
        if ($melding->getMelderNotificaties() === true) {
            $activateSmsErrors->addAll($validator->validate($activateSmsForm['mobielnummer'], new Assert\NotBlank()));
        }

        // handle valid submit
        if ($request->getMethod() === 'POST' && count($activateSmsErrors) === 0) {
            $melding->getMelder()->setMobielNummer($activateSmsForm['mobielnummer']);
            $melding->setMelderNotificaties($activateSmsForm['actief']);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gemeenteamsterdam_rembrandtplein_app_melder_detailmelding', ['meldingUuid' => $melding->getUuid(), 'secret' => $melding->getSecret()]);
        }

        // create response
        return $this->render('RembrandtPleinAppBundle:Melder:detailMelding.html.twig', [
            'melding' => $melding,
            'melderIsSame' => $melderIsSame,
            'activeDienst' => $activeDienst,
            'activateSmsForm' => [
                'data' => $activateSmsForm,
                'errors' => $activateSmsErrors,
                'submitted' => ($request->getMethod() === 'POST')
            ],
            'berichtForm' => [
                'data' => ['bericht' => ''],
                'errors' => [],
                'submitted' => false
            ],
        ]);
    }

    /**
     * @Route("/melder/melding/{meldingUuid}/{secret}/deactiveer-sms-updates")
     * @Method("POST")
     */
    public function deactivateSmsAction(Request $request, CookieToken $cookieToken, $meldingUuid, $secret)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');

        // get Melding object
        $melding = $meldingRepository->get($meldingUuid);
        if ($melding === null) {
            throw $this->createNotFoundException('Melding niet gevonden');
        }
        if ($melding->getSecret() !== $secret) {
            throw $this->createNotFoundException('URL is onjuist');
        }

        $melding->setMelderNotificaties(false);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('gemeenteamsterdam_rembrandtplein_app_melder_detailmelding', ['meldingUuid' => $melding->getUuid(), 'secret' => $melding->getSecret()]);
    }

    /**
     * @Route("/melder/melding/{meldingUuid}/{secret}/reacties")
     */
    public function reactielijstMeldingAction(Request $request, CookieToken $cookieToken, $meldingUuid, $secret)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        /* @var $validator \Symfony\Component\Validator\Validator\ValidatorInterface */
        $validator = $this->get('validator');

        // get Melding object
        $melding = $meldingRepository->get($meldingUuid);
        if ($melding === null) {
            throw $this->createNotFoundException('Melding niet gevonden');
        }
        if ($melding->getSecret() !== $secret) {
            throw $this->createNotFoundException('URL is onjuist');
        }

        $sinceReactieId = $request->query->getInt('sinceReactieId');

        $reacties = $melding->getReacties();
        $reacties = array_filter($reacties, function (Reactie $reactie) use ($sinceReactieId) {
            return $reactie->getId() > $sinceReactieId;
        });

        // create response
        return $this->render('RembrandtPleinAppBundle:Melder:reactielijstMelding.html.twig', [
            'melding' => $melding,
            'reacties' => $reacties, // is without the orginal message
            'form' => [
                'showMobielNummerForm' => $validator->validateProperty($melding->getMelder(), 'mobielNummer')->count() > 0 || $melding->getMelder()->hasNotificatieInformatie() === false,
                'mobielNummer' => $melding->getMelder()->getMobielNummer(),
                'melderNotificaties' => $melding->getMelderNotificaties()
            ],
            'errors' => []
        ]);
    }

    /**
     * @Route("/melder/melding/{meldingUuid}/{secret}/reageer")
     */
    public function addReactieAction(Request $request, CookieToken $cookieToken, $meldingUuid, $secret)
    {
        /* @var $meldingRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\MeldingRepository */
        $meldingRepository = $this->get('rembrandtplein.appbundle.repositories.melding');
        /* @var $dienstRepository \GemeenteAmsterdam\RembrandtPlein\AppBundle\Entity\DienstRepository */
        $dienstRepository = $this->get('rembrandtplein.appbundle.repositories.dienst');
        /* @var $validator \Symfony\Component\Validator\Validator\ValidatorInterface */
        $validator = $this->get('validator');

        // get active dienst
        $activeDienst = $dienstRepository->getActiveDienst();

        // get Melding object
        $melding = $meldingRepository->get($meldingUuid);
        if ($melding === null) {
            throw $this->createNotFoundException('Melding niet gevonden');
        }
        if ($melding->getSecret() !== $secret) {
            throw $this->createNotFoundException('URL is onjuist');
        }

        // check Melder
        $melderIsSame = $melding->getMelder() === $cookieToken->getMelder();

        // sms form
        $activateSmsForm = [
            'actief' => $melding->getMelderNotificaties(),
            'mobielnummer' => $melding->getMelder()->getMobielNummer()
        ];

        // validate sms form
        $activateSmsErrors = new ConstraintViolationList();
        $activateSmsErrors->addAll($validator->validate($activateSmsForm['mobielnummer'], new Assert\Range(['min' => 600000000, 'max' => 699999999, 'minMessage' => 'Ongeldig telefoonnummer, moet starten met 06', 'maxMessage' => 'Ongeldig telefoonnummer, moet starten met 06'])));
        if ($melding->getMelderNotificaties() === true) {
            $activateSmsErrors->addAll($validator->validate($activateSmsForm['mobielnummer'], new Assert\NotBlank()));
        }

        // bericht form
        $berichtForm = [
            'bericht' => $request->request->get('bericht'),
            't0' => $request->request->get('t0'),
            't1' => $request->request->get('t1'),
        ];

        // validate bericht form
        $berichtErrors = new ConstraintViolationList();
        $berichtErrors->addAll($validator->validate($berichtForm['bericht'], new Assert\NotBlank(['message' => 'Vul hieronder uw bericht in'])));

        // is bericht allowed?
        $allowed = $melding->getReactieVanMelderToestaan();
        if ($allowed === false) {
            $expectedT1 = md5($this->container->getParameter('secret') . '|' . explode('|', $berichtForm['t0'])[0] . '|' . 'y');
            $allowed = ($expectedT1 === $berichtForm['t1']);
        }

        // handle submit
        if ($request->getMethod() === 'POST' && count($berichtErrors) === 0 && $allowed) {
            // create Reactie
            $reactie = new Reactie();
            $reactie->setAfzender(Reactie::AFZENDER_MELDER);
            $reactie->setBericht($request->request->get('bericht', ''));
            $reactie->setClientIp($request->getClientIp());
            $reactie->setType(Reactie::TYPE_BERICHT);
            $reactie->setBeoordeling($request->request->get('beoordeling', null));
            $melding->addReactie($reactie);
            $this->getDoctrine()->getManager()->persist($reactie);

            $this->getDoctrine()->getManager()->flush();

            if ($activeDienst !== null && $activeDienst->getHandhaver()->getTelefoon() !== null && $activeDienst->getHandhaver()->getTelefoon() !== '') {
                $this->get('rembrandtplein.appbundle.services.sms')->sentSmsFromTemplate($activeDienst->getHandhaver()->getTelefoon(), 'RembrandtPleinAppBundle:sms:feedbackMelding.txt.twig', ['melding' => $melding, 'reactie' => $reactie]);
            }

            return $this->redirectToRoute('gemeenteamsterdam_rembrandtplein_app_melder_detailmelding', ['meldingUuid' => $melding->getUuid(), 'secret' => $melding->getSecret(), 'new' => $reactie->getId()]);
        }

        // create response
        return $this->render('RembrandtPleinAppBundle:Melder:detailMelding.html.twig', [
            'melding' => $melding,
            'melderIsSame' => $melderIsSame,
            'activeDienst' => $activeDienst,
            'activateSmsForm' => [
                'data' => $activateSmsForm,
                'errors' => $activateSmsErrors,
                'submitted' => false
            ],
            'berichtForm' => [
                'data' => $berichtForm,
                'errors' => $berichtErrors,
                'submitted' => false
            ],
        ]);
    }

    /**
     * @Route("/info/cookies")
     */
    public function cookieInfoAction(Request $request) // important no cookieToken here!
    {
        return $this->render('RembrandtPleinAppBundle:Melder:cookieInfo.html.twig');
    }

    private function formatMobielNummer($mobielnummer) {
        $mobielnummer = preg_replace('/[^0-9]/', '', $mobielnummer); // remove all non digits
        $mobielnummer = substr($mobielnummer, 0, 2) === '31' ? substr($mobielnummer, 2) : $mobielnummer; // remove 31 in the front
        $mobielnummer = substr($mobielnummer, 0, 4) === '0031' ? substr($mobielnummer, 4) : $mobielnummer; // remove 0031 in the front
        $mobielnummer = substr($mobielnummer, 0, 1) === '6' ? '0' . $mobielnummer : $mobielnummer; //
        $mobielnummer = substr($mobielnummer, 0, 2) !== '06' ? '06' . $mobielnummer : $mobielnummer;
        return $mobielnummer;
    }
}
