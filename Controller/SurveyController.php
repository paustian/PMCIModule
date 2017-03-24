<?php

// pnuser.php,v 1.18 2007/03/16 01:58:56 paustian Exp
// ----------------------------------------------------------------------
// PostNuke Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Timothy Paustian
// Purpose of file:  Book user display functions
// ----------------------------------------------------------------------

namespace Paustian\PMCIModule\Controller;

use Zikula\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Paustian\PMCIModule\Entity\SurveyEntity;

/**
 * @Route("/survey")
 */
class SurveyController extends AbstractController {

    /**
     * @Route("")
     * 
     * @param $request
     * @return $response
     */
    public function indexAction(Request $request) {
// Security check
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException(__('You do not have pemission to access the surveys. Please request a copy of the MCI and register.'));
        }

        $response = $this->render('PaustianPMCIModule:Survey:survey_index.html.twig');
        return $response ;
    }

    /**
     *
     * @Route("/edit/{survey}")
     * Edit or Delete a person using the MCI. This allows changes to a person
     * @param $request
     * @param $survey
     * @return $response The rendered output of the modifyconfig template.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function editAction(Request $request, SurveyEntity $survey = null) {
        $doMerge = false;

        $response = $this->render('PaustianPMCIModule:Survey:survey_index.html.twig');
        return $response ;
    }

    /**
     * @Route("/delete/{survey}")
     * @param  $request
     * @param $survey
     * @return $response back to the modification screen
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function deleteAction(Request $request, SurveyEntity $survey) {
        $response = $this->render('PaustianPMCIModule:Survey:survey_index.html.twig');
        return $response ;
    }



    /**
     * @Route("/modify")
     * @param Request $request
     * @return $response
     */
    public function modifyAction(Request $request) {
        $response = $this->render('PaustianPMCIModule:Survey:survey_index.html.twig');
        return $response ;
    }
}
