<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->redirectToRoute('gemeenteamsterdam_rembrandtplein_app_melder_index');
    }

    /**
     * @see http://symfony.com/doc/current/cookbook/routing/redirect_trailing_slash.html
     * @Route(
     *  "/{url}",
     *  requirements={
     *      "url"=".*\/$"
     *  }
     * )
     */
    public function removeTrailingSlashAction(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();
        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);
        return $this->redirect($url, Response::HTTP_TEMPORARY_REDIRECT);
    }
}
