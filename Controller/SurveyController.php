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
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException(__('You do not have pemission to access the surveys. Please request a copy of the MCI and register.'));
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
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException(__('You do not have pemission to access the surveys. Please request a copy of the MCI and register.'));
        }
        if($survey == null){
            return $this->redirect($this->generateUrl('paustianpmcimodule_survey_modify'));
        }
        //Find the person
        $currentUserApi = $this->get('zikula_users_module.current_user');
        $uid = $currentUserApi->get('uid');
        $form = $this->createForm(new Survey(), $survey);

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
     *
     * @Route("/upload/")
     * Upload survey data for the MCI.
     * @param $request
     * @return $response The rendered output of the upload template.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function uploadAction(Request $request) {
       if (!$this->hasPermission($this->name . '::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException(__('You do not have pemission to access the surveys. Please request a copy of the MCI and register.'));
        }
        $survey = new SurveyEntity();

       //Find the person
        $currentUserApi = $this->get('zikula_users_module.current_user');
        $uid = $currentUserApi->get('uid');
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('Paustian\PMCIModule\Entity\PersonEntity')->findOneBy(['userId' => $uid]);
        //Set up some default values for the survey. These can be edited if they need to.
        $survey->setInstitution($person->getInstitution());
        $survey->setCourse($person->getCourse());
        $form = $this->createForm(new SurveyUpload(), $survey);

        $form->handleRequest($request);

        if ($form->isValid()) {
            //Grab the contents of the file.
            $file = $form['file']->getData();
            $extension = $file->guessExtension();
            if($extension == 'csv' || $extension == 'txt'){
                $csv = $this->_parseCsv($file);
                $error = $this->_validateCSV($csv);
                if($error != ''){
                    $this->addFlash('error', "Your csv file is not in the correct format, please re-read the documentation below. If you are using excel, make sure you save in csv format, not UTF-8 csv format: $error");
                    return $this->redirect($this->generateUrl('paustianpmcimodule_survey_upload'));
                }
                //Ok we have valid data so enter the survey data first
                $surveyDate = $form['surveyDate']->getData();
                $prePost = $form['prepost']->getData();
                $survey->setUserId($uid);
                $survey->setPrePost($prePost);
                $survey->setSurveyDate($surveyDate);
                $em->persist($survey);
                //The data needs to be flushed to get the survey ID. Once we have this, we can then
                $em->flush();
                $surveyID = $survey->getId();
               foreach($csv as $studentData){
                    $mciData = new \Paustian\PMCIModule\Entity\MCIDataEntity($studentData);
                    $mciData->setSurveyId($surveyID);
                    $mciData->setRespDate($surveyDate);
                    $em->persist($mciData);
                }
                $em->flush();
                $this->addFlash('status', $this->__('Your survey data has been saved'));
            }
        }
        $response = $this->render('PaustianPMCIModule:Survey:survey_upload.html.twig', array('form' => $form->createView(),));
        return $response ;
    }

    /**
     * Grab the csv file and then arrange it into an array of arrays. Each array has the header values
     * as the keys to the array. I need to make sure this doesn't do bad things if poor text is added.
     * see comments for http://php.net/manual/en/function.file.php for an explanation of this code.
     *
     * @param $file
     * @return array
     */
    private function _parseCsv($file){
        $csv = array_map('str_getcsv', file($file->getPathname()));
        array_walk($csv, function(&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv); # remove column header
        return $csv;
    }

    /**
     * For an array to be valid, it must have the StudentID key and Q1 to Q23
     * this checks for each of those keys
     * @param $cvs
     * @return bool
     */
    private function _validateCSV($csv){
        if(!is_array($csv)){
            return false;
        }
        $firstLine = $csv[0];
        if(!array_key_exists('StudentID', $firstLine)){
            return 'StudentID is missing';
        }
        for($i=1;$i<24;$i++){
            $key = 'Q' . $i;
            if(!array_key_exists($key, $firstLine)){
                return "$key is missing";
            }
        }
        return '';
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
            throw new AccessDeniedException(__('You do not have pemission to delete the surveys. Please request a copy of the MCI and register.'));
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
        $uid = $currentUserApi->get('uid');
        $em = $this->getDoctrine()->getManager();
        $surveys = $em->getRepository("Paustian\PMCIModule\Entity\SurveyEntity")->findBy(['userId' => $uid]);

        $response = $this->render('PaustianPMCIModule:Survey:survey_modify.html.twig', ['surveys' => $surveys]);
        return $response;
    }
}
