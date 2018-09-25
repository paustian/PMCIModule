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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Paustian\PMCIModule\Entity\SurveyEntity;
use Paustian\PMCIModule\Form\Survey;
use Paustian\PMCIModule\Form\SurveyUpload;

/**
 * @Route("/survey")
 */
class SurveyController extends AbstractController {

    /**
     * @Route("")
     * 
     * @param $request
     * @return $response
     *
     * The default action is to submit MCI data into the database. This will date the survey and then absorb all the responses from the MCI data
     * The format is a csv file that contains the questions as downloaded from the a qualtrics survey
     */
    public function indexAction(Request $request) {
    // Security check
        $currentUserApi = $this->get('zikula_users_module.current_user');
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$currentUserApi->isLoggedIn()) {
            $this->addFlash('error', $this->__('You need to register as a user before you can obtain the MCI and then ask for a copy of the MCI before you can do analysis.'));
            return $this->redirect($this->generateUrl('zikulausersmodule_registration_register'));
        }
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            $this->addFlash('error', $this->__('You do not have pemission to access the surveys. You may need to wait until you are authorized by the MCI admin, Timothy Paustian.'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_person_edit'));
        }
        $response = $this->render('PaustianPMCIModule:Survey:survey_index.html.twig');
        return $response ;
    }

    /**
     *
     * @Route("/edit/{survey}")
     * Edit or Delete a survey from MCI. This also deletes all mci data attached to the survey.
     * @param $request
     * @param $survey
     * @return $response The rendered output of the modifyconfig template.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function editAction(Request $request, SurveyEntity $survey=null)
    {
        $currentUserApi = $this->get('zikula_users_module.current_user');
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$currentUserApi->isLoggedIn()) {
            $this->addFlash('error', $this->__('You need to register as a user before you can obtain the MCI.'));
            return $this->redirect($this->generateUrl('zikulausersmodule_registration_register'));
        }
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            $this->addFlash('error', $this->__('You do not have pemission to access the surveys. You may need to wait until you are authorized by the MCI admin, Timothy Paustian.'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
        }
        if($survey == null){
            return $this->redirect($this->generateUrl('paustianpmcimodule_survey_modify'));
        }
        //Find the person
        $currentUserApi = $this->get('zikula_users_module.current_user');
        $uid = $currentUserApi->get('uid');
        $form = $this->createForm(Survey::class, $survey);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $survey->setUserId($uid);
            $em = $this->getDoctrine()->getManager();
            $em->merge($survey);
            //The data needs to be flushed to get the survey ID. Once we have this, we can then
            $em->flush();
            $this->addFlash('status', $this->__('Your survey data has been saved'));
        }
        $response = $this->render('PaustianPMCIModule:Survey:survey_edit.html.twig', array('form' => $form->createView(),));
        return $response;
    }
    /**
     * Upload MCI survey data to the database.
     * @Route("/upload/")
     * Upload survey data for the MCI.
     * @param $request
     * @return $response The rendered output of the upload template.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function uploadAction(Request $request) {
        $currentUserApi = $this->get('zikula_users_module.current_user');
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$currentUserApi->isLoggedIn()) {
            $this->addFlash('error', $this->__('You need to register as a user before you can obtain the MCI and then ask for a copy of the MCI before you can do analysis.'));
            return $this->redirect($this->generateUrl('zikulausersmodule_registration_register'));
        }
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            $this->addFlash('error', $this->__('You do not have pemission to upload a survey. You may need to wait until you are authorized by the MCI admin, Timothy Paustian.'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
        }
        //Find the person
        $em = $this->getDoctrine()->getManager();
        $currentUserApi = $this->get('zikula_users_module.current_user');
        $person = $em->getRepository('Paustian\PMCIModule\Entity\PersonEntity')->getCurrentPerson($currentUserApi);
        if($person == null){
            $this->addFlash('error', $this->__('You have to register first before you can upload surveys'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_person_edit'));
        }
        $survey = new SurveyEntity();
        $survey->setInstitution($person->getInstitution());
        $survey->setCourse($person->getCourse());
        //Set up some default values for the survey. These can be edited if they need to.
        $form = $this->createForm(SurveyUpload::class, $survey);

        $form->handleRequest($request);

        if ($form->isValid()) {
            //Grab the contents of the file.
            $file = $form['file']->getData();
            $extension = $file->guessExtension();
            $surveyRepo = $em->getRepository('Paustian\PMCIModule\Entity\SurveyEntity');
            if($extension == 'csv' || $extension == 'txt'){
                $csv = $surveyRepo->parseCsv($file);
                $error = $surveyRepo->validateCSV($csv);
                if($error != ''){
                    $this->addFlash('error', "Your csv file is not in the correct format, please re-read the documentation below. If you are using excel, make sure you save in csv format, not UTF-8 csv format: $error");
                    return $this->redirect($this->generateUrl('paustianpmcimodule_survey_upload'));
                }

                //Ok we have valid data so enter the survey data first
                $surveyDate = $form['surveyDate']->getData();
                $prePost = $form['prepost']->getData();
                $uid = $currentUserApi->get('uid');
                $survey->setUserId($uid);
                $survey->setPrePost($prePost);
                $survey->setSurveyDate($surveyDate);
                $em->persist($survey);
                //The data needs to be flushed to get the survey ID. Once we have this, we can then
                $em->flush();
                $surveyID = $survey->getId();
                $itemCounter = 0;
                foreach($csv as $studentData){
                    $itemCounter++;
                    if($this->_validateStudentDataRow($studentData, $itemCounter)){
                        $mciData = new \Paustian\PMCIModule\Entity\MCIDataEntity($studentData);
                        $mciData->setSurveyId($surveyID);
                        $mciData->setRespDate($surveyDate);
                        $em->persist($mciData);
                    }
                }
                $em->flush();
                $this->addFlash('status', $this->__('Your survey data has been saved'));
            }
        }
        $response = $this->render('PaustianPMCIModule:Survey:survey_upload.html.twig', array('form' => $form->createView(),));
        return $response ;
    }

    private function _validateStudentDataRow($studentData, $itemCounter){
        if($studentData === false){
            $this->addFlash('error', $this->__("Row $itemCounter did not parse correctly. Make sure you hve it formatted correctly." ));
            return false;
        }
        //This checks to make sure they are all numbers, except for Major, that is text.
        //This will also flag any empty values, as those will come back as strings.
        if( (count($studentData) - 1) != count(array_filter($studentData, 'is_numeric')) ){
            $this->addFlash('error', $this->__("Item $itemCounter was missing values. Make sure all columns have values and that they are integers, except for the Major Column." ));
            return false;
        }
        return true;
    }

    /**
     * @Route("/delete/{survey}")
     * @param  $request
     * @param $survey
     * @return $response back to the modification screen
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function deleteAction(Request $request, SurveyEntity $survey) {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_DELETE)) {
            $this->addFlash('error', $this->__('You do not have pemission to delete the surveys. Please request a copy of the MCI and register.'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
        }
        //we need to delete all the responses from this survey also
        //To conserve memory and do this quickly we write it in dql
        $em = $this->getDoctrine()->getManager();
        $surveyId = $survey->getId();
        $q =  $em->createQuery("delete from Paustian\PMCIModule\Entity\MCIDataEntity m where m.surveyId = " . $surveyId);
        $numDeleted = $q->execute();

        //now delete the survey
        $em->remove($survey);
        $em->flush();

        $this->addFlash('status', "Your survey was deleted, along with $numDeleted survey responses.");

        //finally redirect to the modify interface
        return $this->redirect($this->generateUrl('paustianpmcimodule_survey_modify'));
    }



    /**
     * @Route("/modify")
     * @param Request $request
     * @return $response
     */
    public function modifyAction(Request $request) {
        $currentUserApi = $this->get('zikula_users_module.current_user');
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$currentUserApi->isLoggedIn()) {
            $this->addFlash('error', $this->__('You are not authorized to modify any users. Please log in first and then you can modify your information'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
        }
        $uid = $currentUserApi->get('uid');
        $em = $this->getDoctrine()->getManager();
        $surveys = null;
        if($this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)){
            $surveys = $em->getRepository("Paustian\PMCIModule\Entity\SurveyEntity")->findAll();
        }else {
            $surveys = $em->getRepository("Paustian\PMCIModule\Entity\SurveyEntity")->findBy(['userId' => $uid]);
        }

        $response = $this->render('PaustianPMCIModule:Survey:survey_modify.html.twig', ['surveys' => $surveys]);
        return $response;
    }
}
