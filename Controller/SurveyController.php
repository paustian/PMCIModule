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


use Doctrine\ORM\EntityManagerInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Symfony\Component\Routing\RouterInterface;
use Paustian\PMCIModule\Entity\SurveyEntity;
use Paustian\PMCIModule\Form\Survey;
use Paustian\PMCIModule\Form\SurveyUpload;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/survey")
 */
class SurveyController extends AbstractController {

    private $currentUserApi;
    private $em;

    public function __construct(
        AbstractExtension $extension,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        CurrentUserApiInterface $currentUserApi,
        EntityManagerInterface $entityManagerInterface
    ){
        $this->currentUserApi = $currentUserApi;
        $this->em = $entityManagerInterface;
        parent::__construct($extension, $permissionApi, $variableApi, $translator);
    }
    /**
     * @Route("")
     *
     * @param $request
     * @return $response
     *
     * The default action is to submit MCI data into the database. This will date the survey and then absorb all the responses from the MCI data
     * The format is a csv file that contains the questions as downloaded from the a qualtrics survey
     */
    public function index(Request $request) {
    // Security check
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$this->currentUserApi->isLoggedIn()) {
            $this->addFlash('error', $this->trans('You need to register as a user before you can obtain the MCI and then ask for a copy of the MCI before you can do analysis.'));
            return $this->redirect($this->generateUrl('zikulausersmodule_registration_register'));
        }
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            $this->addFlash('error', $this->trans('You do not have pemission to access the surveys. You may need to wait until you are authorized by the MCI admin, Timothy Paustian.'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_person_edit'));
        }
        return $this->redirect($this->generateUrl('paustianpmcimodule_survey_modify'));
    }

    /**
     *
     * @Route("/edit/{survey}")
     *
     * Edit or Delete a survey from MCI. This also deletes all mci data attached to the survey.
     * @param $request
     * @param $survey
     * @return $response The rendered output of the modifyconfig template.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function edit(Request $request, SurveyEntity $survey=null)
    {
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$this->currentUserApi->isLoggedIn()) {
            $this->addFlash('error', $this->trans('You need to register as a user before you can obtain the MCI.'));
            return $this->redirect($this->generateUrl('zikulausersmodule_registration_register'));
        }
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            $this->addFlash('error', $this->trans('You do not have pemission to access the surveys. You may need to wait until you are authorized by the MCI admin, Timothy Paustian.'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
        }
        if($survey == null){
            return $this->redirect($this->generateUrl('paustianpmcimodule_survey_modify'));
        }
        //Find the person
        $uid = $this->currentUserApi->get('uid');
        $form = $this->createForm(Survey::class, $survey);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $survey->setUserId($uid);
            $this->em->merge($survey);
            //The data needs to be flushed to get the survey ID. Once we have this, we can then
            $this->em->flush();
            $this->addFlash('status', $this->trans('Your survey data has been saved'));
        }
        $response = $this->render('@PaustianPMCIModule/Survey/survey_edit.html.twig', array('form' => $form->createView(),));
        return $response;
    }

    /**
     *
     * @Route("/view/{survey}")
     *
     * View responses to a survey, especially giving the number of responses
     * @param $request
     * @param $survey
     */
    public function view(Request $request, int $survey){
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$this->currentUserApi->isLoggedIn()) {
            $this->addFlash('error', $this->trans('You need to log in or register as a user before see your surveys.'));
            return $this->redirect($this->generateUrl('zikulausersmodule_registration_register'));
        }
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            $this->addFlash('error', $this->trans('You do not have pemission to access the surveys. You may need to wait until you are authorized by the MCI admin, Timothy Paustian.'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
        }

        $surRepo = $this->getDoctrine()->getManager()->getRepository('Paustian\PMCIModule\Entity\MCIDataEntity');
        $surData = $surRepo->findBy(['surveyId' => $survey]);
        $numStudents = count($surData);
        $scores = [];
        $key = $surRepo->getKey();
        $totalPoints = 0;
        foreach($surData as $indSurvey){
            $indSurveyArray = $indSurvey->toArray();
            $inScore = $surRepo->gradeStudent($indSurveyArray, $key);
            $scores[] = round($inScore, 2);
            $totalPoints += $inScore;
        }
        $average = $totalPoints/$numStudents;
        return $this->render('@PaustianPMCIModule/Survey/survey_view.html.twig', [
            'surData' => $surData,
            'numStudents' => $numStudents,
            'scores' => $scores,
            'average' => $average,
            'surveyId'=> $survey
        ]);

    }
    /**
     * Upload MCI survey data to the database.
     *
     * @Route("/upload/")
     * Upload survey data for the MCI.
     * @param $request
     * @return $response The rendered output of the upload template.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function upload(Request $request) {
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$this->currentUserApi->isLoggedIn()) {
            $this->addFlash('error', $this->trans('You need to register as a user before you can obtain the MCI and then ask for a copy of the MCI before you can do analysis.'));
            return $this->redirect($this->generateUrl('zikulausersmodule_registration_register'));
        }
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            $this->addFlash('error', $this->trans('You do not have pemission to upload a survey. You may need to wait until you are authorized by the MCI admin, Timothy Paustian.'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
        }
        //Find the person
        $person = $this->em->getRepository('Paustian\PMCIModule\Entity\PersonEntity')->getCurrentPerson($this->currentUserApi);
        if($person == null){
            $this->addFlash('error', $this->trans('You have to register first before you can upload surveys'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_person_edit'));
        }
        $survey = new SurveyEntity();
        $survey->setInstitution($person->getInstitution());
        $survey->setCourse($person->getCourse());
        //Set up some default values for the survey. These can be edited if they need to.
        $form = $this->createForm(SurveyUpload::class, $survey);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            //Grab the contents of the file.
            $file = $form['file']->getData();
            $extension = $file->guessExtension();
            $surveyRepo = $this->em->getRepository('Paustian\PMCIModule\Entity\SurveyEntity');
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
                $uid = $this->currentUserApi->get('uid');
                $survey->setUserId($uid);
                $survey->setPrePost($prePost);
                $survey->setSurveyDate($surveyDate);
                $this->em->persist($survey);
                //The data needs to be flushed to get the survey ID. Once we have this, we can then
                $this->em->flush();
                $surveyID = $survey->getId();
                $itemCounter = 0;
                foreach($csv as $studentData){
                    $itemCounter++;
                    if($this->_validateStudentDataRow($studentData, $itemCounter)){
                        $mciData = new \Paustian\PMCIModule\Entity\MCIDataEntity($studentData);
                        $mciData->setSurveyId($surveyID);
                        $mciData->setRespDate($surveyDate);
                        $this->em->persist($mciData);
                    }
                }
                $this->em->flush();
                $this->addFlash('status', $this->trans('Your survey data has been saved'));
            }
        }
        return $this->render('@PaustianPMCIModule/Survey/survey_upload.html.twig', array('form' => $form->createView(),));
    }

    private function _validateStudentDataRow($studentData, $itemCounter){
        if($studentData === false){
            $this->addFlash('error', $this->trans("Row $itemCounter did not parse correctly. Make sure you hve it formatted correctly." ));
            return false;
        }
        //This checks to make sure they are all numbers, except for Major, that is text.
        //This will also flag any empty values, as those will come back as strings.
        if( (count($studentData) - 1) != count(array_filter($studentData, 'is_numeric')) ){
            $this->addFlash('error', $this->trans("Item $itemCounter was missing values. Make sure all columns have values and that they are integers, except for the Major Column." ));
            return false;
        }
        return true;
    }

    /**
     * @Route("/delete/{survey}")
     *
     * @param  $request
     * @param $survey
     * @return $response back to the modification screen
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function delete(Request $request, SurveyEntity $survey) {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_DELETE)) {
            $this->addFlash('error', $this->trans('You do not have pemission to delete the surveys. Please request a copy of the MCI and register.'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
        }
        //we need to delete all the responses from this survey also
        //To conserve memory and do this quickly we write it in dql
        $surveyId = $survey->getId();
        $q =  $this->em->createQuery("delete from Paustian\PMCIModule\Entity\MCIDataEntity m where m.surveyId = " . $surveyId);
        $numDeleted = $q->execute();

        //now delete the survey
        $this->em->remove($survey);
        $this->em->flush();

        $this->addFlash('status', "Your survey was deleted, along with $numDeleted survey responses.");

        //finally redirect to the modify interface
        return $this->redirect($this->generateUrl('paustianpmcimodule_survey_modify'));
    }



    /**
     * @Route("/modify")
     * @Theme("admin")
     * @param Request $request
     * @return $response
     */
    public function modify(Request $request) {
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$this->currentUserApi->isLoggedIn()) {
            $this->addFlash('error', $this->trans('You are not authorized to modify any users. Please log in first and then you can modify your information'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
        }
        $uid = $this->currentUserApi->get('uid');
        $this->em = $this->getDoctrine()->getManager();
        $surveys = null;
        if($this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)){
            $surveys = $this->em->getRepository("Paustian\PMCIModule\Entity\SurveyEntity")->findAll();
        }else {
            $surveys = $this->em->getRepository("Paustian\PMCIModule\Entity\SurveyEntity")->findBy(['userId' => $uid]);
        }

        return $this->render('@PaustianPMCIModule/Survey/survey_modify.html.twig', ['surveys' => $surveys]);
    }
}
