<?php
/**
 * Created by PhpStorm.
 * User: paustian
 * Date: 3/29/17
 * Time: 3:00 PM
 */

namespace Paustian\PMCIModule\Controller;

use Paustian\PMCIModule\Entity\PersonEntity;
use Paustian\PMCIModule\Entity\SurveyEntity;
use Paustian\PMCIModule\PaustianPMCIModule;
use Zikula\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove


class AnalysisController extends AbstractController {
    /**
     * @Route("")
     * @param $request - the incoming request.
     * The main entry point
     *
     * @return $response The rendered output consisting mainly of the admin menu
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function indexAction(Request $request) {
        //security check
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_COMMENT)) {
            throw new AccessDeniedException(__('You do not have pemission to access the analysis interface.'));
        }

        $form = $this->createForm(new \Paustian\PMCIModule\Form\Analysis());

        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $currentUserApi = $this->get('zikula_users_module.current_user');
        $person = $em->getRepository('Paustian\PMCIModule\Entity\PersonEntity')->getCurrentPerson($currentUserApi);
        $mciRepo = $em->getRepository('Paustian\PMCIModule\Entity\MCIDataEntity');
        $removeSurvey1 = $removeSurvey2 = false;

        if ($form->isValid()) {
            $match = $form->get('match')->getData();
            //Grab the presurvey
            $survey1 = $form->get('survey1')->getData();

            //no survey was chosen so see if they picked a file to upload
            if(!$survey1){
                $file1 = $form['file1']->getData();
                if($file1 == null){
                    $this->addFlash('error', "You need to either choose a survey from the menu or upload a file in the correct format.");
                    return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
                }
                $csv1 = $this->_getSurveyData($file1);
                if($csv1 == null){
                    $this->addFlash('error', "Your pre csv file is not in the correct format, please re-read the documentation below. If you are using excel, make sure you save in csv format, not UTF-8 csv format: $csv1");
                    return $this->redirect($this->generateUrl('paustianpmcimodule_survey_upload'));
                }
                $removeSurvey1 = true;
                //create new survey. We will delete it at the end of this function
                $survey1 = $this->_createSurvey($csv1, $person, new \DateTime());
            }
            $survey2 = $form->get('survey2')->getData();
            if(!$survey2){
                $file2 = $form['file2']->getData();
                if($file2 == null){
                    $this->addFlash('error', "You need to either choose a survey from the menu or upload a file in the correct format.");
                    return $this->redirect($this->generateUrl('paustianpmcimodule_index'));
                }
                $csv2 = $this->_getSurveyData($file2);
                if(!is_array($csv2)){
                    $this->addFlash('error', "Your post csv file is not in the correct format, please re-read the documentation below. If you are using excel, make sure you save in csv format, not UTF-8 csv format: $csv2");
                    return $this->redirect($this->generateUrl('paustianpmcimodule_survey_upload'));
                }
                $removeSurvey2 = true;
                //create new survey. We will delete it at the end of this function
                $survey2 = $this->_createSurvey($csv2, $person, new \DateTime());
            }
            $lgstudents = $form->get('lgstudents')->getData();
            //These are all getting grabbed correctly. Uncomment them as you
            $lgtest = $form->get('lgtest')->getData();
            //$itemDifficulty = $form->get('diff')->getData();
            //$itemDiscrimination = $form->get('discrim')->getData();
            //$pbc = $form->get('pbc')->getData();
            $options = [];
            $race = $form->get('race')->getData();
            if($race != 0){
                $options['race'] = $race;
            }
            $sex = $form->get('sex')->getData();
            if($sex != 0){
                $options['sex'] = $sex;
            }
            $esl = $form->get('esl')->getData();
            if($esl != 0){
                $options['esl'] = $esl;
            }
            $em = $this->getDoctrine()->getManager();
            //grab the key from the database. This is the first entry.
            $key = $mciRepo->getKey();
            if($match){
                $matchedStudents = $mciRepo->matchStudents($survey1, $survey2, $options);
                $avgTestGrades = $mciRepo->gradeMatchedStudents($matchedStudents, $key);
                if($lgstudents){
                    $avgLg = $mciRepo->calcStudentLearningGains($matchedStudents);
                    if($avgLg === false){
                        $this->addFlash('error', $this->__('Something is wrong in how you set up to calculate learning gains.'));
                        return $this->redirect($this->generateUrl('paustianpmcimodule_pmci'));
                    }
                }
            }
            if($lgtest){
                //determine the learning gain for each item and then group them using Fundamental Statements from ASM
                $testItemResults = $mciRepo->calculateItemLearningGains($survey1, $survey2, $key, $options);
            }
            $response = $this->render('PaustianPMCIModule:Analysis:pmci_analysis_results.html.twig', [
                    'studentData' => $matchedStudents,
                    'lgstudents' => $lgstudents,
                    'lgavg'=> $avgLg,
                    'avgTestGrades' => $avgTestGrades,
                    'testItemResults' => $testItemResults,
                ]);
            //If the MCI data was uploaded, remove the MCI data and the survey data
            if($removeSurvey1){
                $q = $em->createQuery('delete from Paustian\PMCIModule\Entity\MCIDataEntity m where m.surveyId =' . $survey1->getId());
                $q->execute();
                $em->remove($survey1);
            }
            if($removeSurvey2){
                $q = $em->createQuery('delete from Paustian\PMCIModule\Entity\MCIDataEntity m where m.surveyId =' . $survey2->getId());
                $q->execute();
                $em->remove($survey2);
            }
            $em->flush();
            return $response;
        }
        //@todo Add menus to limit by age, gpa. Use a number box, with limits and a > = < sign. This can then be made into an option
        // Return a page of menu items.
        $response = $this->render('PaustianPMCIModule:Analysis:pmci_analysis_index.html.twig', [
                'form' => $form->createView(),]);
        return $response;
    }

    /**
     * @param $inFile
     * @return mixed
     */
    private function _getSurveyData($inFile){
        $em = $this->getDoctrine()->getManager();
        $surveyRepo = $em->getRepository('Paustian\PMCIModule\Entity\SurveyEntity');
        $csv = $surveyRepo->parseCsv($inFile);
        $error = $surveyRepo->validateCSV($csv);
        if($error != ''){
            return $error;
        }
        return $csv;
    }

    private function _createSurvey($csv, PersonEntity $person, $date, $preSurvey=true){
        //create the survey
        $survey = new SurveyEntity();
        $survey->setInstitution($person->getInstitution());
        $survey->setCourse($person->getCourse());
        $survey->setUserId($person->getUserId());
        $survey->setPrePost($preSurvey);
        $survey->setSurveyDate($date);
        $em = $this->getDoctrine()->getManager();
        $em->persist($survey);
        //The data needs to be flushed to get the survey ID. Once we have this, we can then
        $em->flush();
        $surveyID = $survey->getId();

        //now persist the data from the survey
        foreach($csv as $studentData){
            $mciData = new \Paustian\PMCIModule\Entity\MCIDataEntity($studentData);
            $mciData->setSurveyId($surveyID);
            $mciData->setRespDate($date);
            $em->persist($mciData);
        }
        $em->flush();
        return $survey;
    }
}