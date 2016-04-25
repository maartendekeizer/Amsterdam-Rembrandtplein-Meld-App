<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace GemeenteAmsterdam\RembrandtPlein\AppBundle\Utils;

class SecretGenerator
{
    public function generate()
    {
        $randomNumber1 = rand(1000000, 9999999);
        $randomNumber2 = rand(1000000, 9999999);
        $randomNumber3 = rand(1000000, 9999999);

        return vsprintf('%x%x%x', [$randomNumber1, $randomNumber2, $randomNumber3]);
    }
}