<?php
/**
 * Created by PhpStorm.
 * User: paustian
 * Date: 3/29/17
 * Time: 3:00 PM
 */

namespace Paustian\PMCIModule\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Paustian\PMCIModule\Entity\PersonEntity;
use Paustian\PMCIModule\Entity\SurveyEntity;
use Paustian\PMCIModule\Form\Analysis;
use Paustian\PMCIModule\Form\MatchStudent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Api\CurrentUserApi;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

// used in annotations - do not remove


class AnalysisController extends AbstractController {

    public function __construct(AbstractExtension $extension,
                                PermissionApiInterface $permissionApi,
                                VariableApiInterface $variableApi,
                                TranslatorInterface $translator,
                                CurrentUserApi $currentUserApi,
                                EntityManagerInterface $entityManagerInterface){
        parent::__construct($extension, $permissionApi, $variableApi, $translator);
        $this->currUser = $currentUserApi;
        $this->em = $entityManagerInterface;
    }
    /**
     * @Route("")
     * @param $request - the incoming request.
     * The main entry point
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)  {
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$this->currUser->isLoggedIn()) {
            $this->addFlash('error', $this->trans('You need to register as a user before you can obtain the MCI and then ask for a copy of the MCI before you can do analysis.'));
            return $this->redirect($this->generateUrl('zikulausersmodule_registration_register'));
        }

        //security check
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_EDIT)) {
            $this->addFlash('error', $this->trans('You do not have pemission to access the surveys. You may need to wait until you are authorized by the MCI admin, Timothy Paustian.'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_person_edit'));
        }

        $qb = $this->em->createQueryBuilder('u');
        $qb->select('u')
            ->from('Paustian\PMCIModule\Entity\SurveyEntity', 'u');
        //restrict access to only your surveys unless you have admin access to this module
        $qb->where($qb->expr()->eq('u.userId', ":uid"))
                ->setParameter("uid", $this->currUser->get('uid'));
        $query = $qb->getQuery();
        $results = $query->getResult();

        $form = $this->createForm(Analysis::class, ['choiceOptions' => $results]);

        $form->handleRequest($request);

        $person = $this->em->getRepository('Paustian\PMCIModule\Entity\PersonEntity')->getCurrentPerson($this->currUser);
        $mciRepo = $this->em->getRepository('Paustian\PMCIModule\Entity\MCIDataEntity');
        $removeSurvey1 = $removeSurvey2 = false;

        if($form->isSubmitted() && $form->isValid()) {
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
                    return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
                }
                $removeSurvey1 = true;
                //create new survey. We will delete it at the end of this function
                $survey1 = $this->_createSurvey($csv1, $person, new \DateTime());
            }
            $survey1Name = $survey1->getDisplayName();
            $survey2 = $form->get('survey2')->getData();
            if(!$survey2){
                $file2 = $form['file2']->getData();
                if($file2 == null){
                    $this->addFlash('error', "You need to either choose a survey from the menu or upload a file in the correct format.");
                    return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
                }
                $csv2 = $this->_getSurveyData($file2);
                if(!is_array($csv2)){
                    $this->addFlash('error', "Your post csv file is not in the correct format, please re-read the documentation below. If you are using excel, make sure you save in csv format, not UTF-8 csv format: $csv2");
                    return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
                }
                $removeSurvey2 = true;
                //create new survey. We will delete it at the end of this function
                $survey2 = $this->_createSurvey($csv2, $person, new \DateTime());
            }
            $survey2Name = $survey2->getDisplayName();
            $lgstudents = $form->get('lgstudents')->getData();
            $lgtest = $form->get('lgtest')->getData();
            $itemDiscrim = $form->get('discrim')->getData();
            $pbc = $form->get('pbc')->getData();

            $options = [];
            $race = $form->get('race')->getData();
            if($race != 0){
                $options[] = ['=', 'race', $race];
            }
            $sex = $form->get('sex')->getData();
            if($sex != 0){
                $options[] = ['=', 'sex', $sex];
            }
            $esl = $form->get('esl')->getData();
            if($esl != 0){
                $options[] = ['=', 'esl', $esl];
            }
            $operators = ['=', '>', '<'];
            $ageOpt = $form->get('ageOpt')->getData();
            $age = $form->get('age')->getData();
            if(($ageOpt !== null) && ($age !== null)){
                $options[] = [$operators[$ageOpt], 'age', $age];
            }
            $gpa = $form->get('gpa')->getData();
            if($gpa !== null){
                $options[] = ['=', 'gpa', $gpa];
            }

            //grab the key from the database. This is the first entry.
            $key = $mciRepo->getKey();
            $matchedStudents = [];
            $avgTestGrades = [];
            $avgLg = 0;
            if($match){
                $matchedStudents = $mciRepo->matchStudents($survey1, $survey2, $options);
                $avgTestGrades = $mciRepo->gradeMatchedStudents($matchedStudents, $key);
                if($lgstudents){
                    $avgLg = $mciRepo->calcStudentLearningGains($matchedStudents);
                    if($avgLg === false){
                        $this->addFlash('error', $this->trans('Something is wrong in how you set up to calculate learning gains.'));
                        return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
                    }
                }
            }
            $testItemResults = [];
            if($lgtest){
                //determine the learning gain for each item and then group them using Fundamental Statements from ASM
                $testItemResults = $mciRepo->calculateItemLearningGains($survey1, $survey2, $key, $options);
            }
            $preTestItemDisc = [];
            $postTestItemDisc = [];
            if($itemDiscrim){
                $preTestItemDisc = $mciRepo->calcItemDiscrim($survey1, $options);
                $postTestItemDisc = $mciRepo->calcItemDiscrim($survey2, $options);
            }
            $preTestItemPbc = [];
            $postTestItemPbc = [];
            if($pbc){
                $preTestItemPbc = $mciRepo->calculatePbc($survey1, $options);
                $postTestItemPbc = $mciRepo->calculatePbc($survey2, $options);
            }

            $response = $this->render('@PaustianPMCIModule/Analysis/pmci_analysis_results.html.twig', [
                    'match' => $match,
                    'studentData' => $matchedStudents,
                    'lgstudents' => $lgstudents,
                    'lgavg'=> $avgLg,
                    'avgTestGrades' => $avgTestGrades,
                    'testItemResults' => $testItemResults,
                    'pbc' => $pbc,
                    'preTestItemPbc' => $preTestItemPbc,
                    'postTestItemPbc' => $postTestItemPbc,
                    'itemD'=> $itemDiscrim,
                    'preTestItemDisc' => $preTestItemDisc,
                    'postTestItemDisc' => $postTestItemDisc,
                    'survey1Name' => $survey1Name,
                    'survey2Name' => $survey2Name
                ]);
            //If the MCI data was uploaded, remove the MCI data and the survey data
            if($removeSurvey1){
                $q = $this->em->createQuery('delete from Paustian\PMCIModule\Entity\MCIDataEntity m where m.surveyId =' . $survey1->getId());
                $q->execute();
                $this->em->remove($survey1);
            }
            if($removeSurvey2){
                $q = $this->em->createQuery('delete from Paustian\PMCIModule\Entity\MCIDataEntity m where m.surveyId =' . $survey2->getId());
                $q->execute();
                $this->em->remove($survey2);
            }
            $this->em->flush();
            return $response;
        }

        // Return a page of menu items.
        $response = $this->render('@PaustianPMCIModule/Analysis/pmci_analysis_index.html.twig', [
                'form' => $form->createView(),]);
        return $response;
    }

    /**
     * @param $inFile
     * @return array
     */

    private function _getSurveyData($inFile) {
        $surveyRepo = $this->em->getRepository('Paustian\PMCIModule\Entity\SurveyEntity');
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
        $this->em->persist($survey);
        //The data needs to be flushed to get the survey ID. Once we have this, we can then
        $this->em->flush();
        $surveyID = $survey->getId();

        //now persist the data from the survey
        foreach($csv as $studentData){
            $mciData = new \Paustian\PMCIModule\Entity\MCIDataEntity($studentData);
            $mciData->setSurveyId($surveyID);
            $mciData->setRespDate($date);
            $this->em->persist($mciData);
        }
        $this->em->flush();
        return $survey;
    }

    /**
     * @Route("getmatchedsurveys")
     * @param Request $request
     * @return Response
     */
    public function getMatchedSurveys(Request $request){
        if(!$this->currUser->isLoggedIn()) {
            $this->addFlash('error', $this->trans('You need to register as a user before you can obtain the MCI and then ask for a copy of the MCI before you can do analysis.'));
            return $this->redirect($this->generateUrl('zikulausersmodule_registration_register'));
        }

        //security check
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_EDIT)) {
            $this->addFlash('error', $this->trans('You do not have pemission to access the surveys. You may need to wait until you are authorized by the MCI admin, Timothy Paustian.'));
            return $this->redirect($this->generateUrl('paustianpmcimodule_person_edit'));
        }
        $qb = $this->em->createQueryBuilder('u');
        $qb->select('u')
            ->from('Paustian\PMCIModule\Entity\SurveyEntity', 'u');
        //restrict access to only your surveys unless you have admin access to this module
        $qb->where($qb->expr()->eq('u.userId', ":uid"))
            ->setParameter("uid", $this->currUser->get('uid'));
        $query = $qb->getQuery();
        $results = $query->getResult();

        $form = $this->createForm(MatchStudent::class, ['choiceOptions' => $results]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            //Grab the presurvey
            $survey1 = $form->get('survey1')->getData();
            $survey2 = $form->get('survey2')->getData();
            $mciRepo = $this->em->getRepository('Paustian\PMCIModule\Entity\MCIDataEntity');
            $matchedStudents = $mciRepo->matchStudents($survey1, $survey2);
            //Grab the key for grading.
            $answerKey = $mciRepo->getKey();
            $csvFile = "id,pre-score,post-score,pre-q1,post-q1,pre-q2,post-q2,pre-q3,post-q3,pre-q4,post-q4,pre-q5,post-q5,pre-q6,post-q6,pre-q7,post-q7,pre-q8,post-q8,pre-q9,post-q9,pre-q10,post-q11,pre-q11,post-q11,pre-q12,post-q12,pre-q13,post-q13,pre-q14,post-q14,pre-q15,post-q15,pre-q16,post-q16,pre-q17,post-q17,pre-q18,post-q18,pre-q19,post-q19,pre-q20,post-q20,pre-q21,post-q21,pre-q22,post-q22,pre-q23,post-q23,gpa,sex,race,age,esl,college\n";
            foreach($matchedStudents as $key => $mStudent){
                $csvFile .= $key . ",";
                $csvFile .= $mciRepo->gradeStudent($mStudent['pre']->toArray(), $answerKey) . ",";
                $csvFile .= $mciRepo->gradeStudent($mStudent['post']->toArray(), $answerKey) . ",";
                $csvFile .= $mStudent['pre']->getQ1Resp() . ",";
                $csvFile .= $mStudent['post']->getQ1Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ2Resp() . ",";
                $csvFile .= $mStudent['post']->getQ2Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ3Resp() . ",";
                $csvFile .= $mStudent['post']->getQ3Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ4Resp() . ",";
                $csvFile .= $mStudent['post']->getQ4Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ5Resp() . ",";
                $csvFile .= $mStudent['post']->getQ5Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ6Resp() . ",";
                $csvFile .= $mStudent['post']->getQ6Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ7Resp() . ",";
                $csvFile .= $mStudent['post']->getQ7Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ8Resp() . ",";
                $csvFile .= $mStudent['post']->getQ8Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ9Resp() . ",";
                $csvFile .= $mStudent['post']->getQ9Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ10Resp() . ",";
                $csvFile .= $mStudent['post']->getQ10Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ11Resp() . ",";
                $csvFile .= $mStudent['post']->getQ11Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ12Resp() . ",";
                $csvFile .= $mStudent['post']->getQ12Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ13Resp() . ",";
                $csvFile .= $mStudent['post']->getQ13Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ14Resp() . ",";
                $csvFile .= $mStudent['post']->getQ14Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ15Resp() . ",";
                $csvFile .= $mStudent['post']->getQ15Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ16Resp() . ",";
                $csvFile .= $mStudent['post']->getQ16Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ17Resp() . ",";
                $csvFile .= $mStudent['post']->getQ17Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ18Resp() . ",";
                $csvFile .= $mStudent['post']->getQ18Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ19Resp() . ",";
                $csvFile .= $mStudent['post']->getQ19Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ20Resp() . ",";
                $csvFile .= $mStudent['post']->getQ20Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ21Resp() . ",";
                $csvFile .= $mStudent['post']->getQ21Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ22Resp() . ",";
                $csvFile .= $mStudent['post']->getQ22Resp() . ",";
                $csvFile .= $mStudent['pre']->getQ23Resp() . ",";
                $csvFile .= $mStudent['post']->getQ23Resp() . ",";
                $gpa = $mStudent['pre']->getGpa();
                switch ($gpa){
                    case 1:
                        $csvFile .= ">3.5". ",";
                         break;
                    case 2:
                        $csvFile .= "3.0 - 3.49" . ",";
                        break;
                    case 3:
                        $csvFile .= "2.5 - 2.99" . ",";
                        break;
                    case 4:
                        $csvFile .= "2.0 - 2.49" . ",";
                        break;
                    case 5:
                        $csvFile .= "below 2.0" . ",";
                        break;
                    default:
                        $csvFile .= "NR" . ",";
                }
                $sex = $mStudent['pre']->getSex();
                switch ($sex){
                    case 1:
                        $csvFile .= "male" . ",";
                        break;
                    case 2:
                        $csvFile .= "female" . ",";
                        break;
                    case 3:
                        $csvFile .= "other" . ",";
                        break;
                    default:
                        $csvFile .= "NR" . ",";
                }
                $race = $mStudent['pre']->getRace();
                switch ($race){
                    case 1:
                        $csvFile .= "American Indian/Alaskan Native" . ",";
                        break;
                    case 2:
                        $csvFile .= "Black or African American" . ",";
                        break;
                    case 3:
                        $csvFile .= "Asian or Pacific Islander" . ",";
                        break;
                    case 4:
                        $csvFile .= "Hispanic/Latino" . ",";
                        break;
                    case 5:
                        $csvFile .= "White" . ",";
                        break;
                    default:
                        $csvFile .= "NR" . ",";
                }
                $age = $mStudent['pre']->getAge();
                switch ($age){
                    case 1:
                        $csvFile .= "18-20" . ",";
                        break;
                    case 2:
                        $csvFile .= "21-25" . ",";
                        break;
                    case 3:
                        $csvFile .= "26-30" . ",";
                        break;
                    case 4:
                        $csvFile .= "31-35" . ",";
                        break;
                    case 5:
                        $csvFile .= "36-45" . ",";
                        break;
                    case 6:
                        $csvFile .= "46-55" . ",";
                        break;
                    case 7:
                        $csvFile .= "56-65" . ",";
                        break;
                    case 8:
                        $csvFile .= ">65" . ",";
                        break;
                    default:
                        $csvFile .= "NR" . ",";
                }
                $esl = $mStudent['pre']->getEsl();
                switch ($esl){
                    case 1:
                        $csvFile .= "Yes" . ",";
                        break;
                    case 2:
                        $csvFile .= "No" . ",";
                        break;
                    default:
                        $csvFile .= "NR" . ",";
                }
                $college = $mStudent['pre']->getSchool();
                switch ($college){
                    case 1:
                        $csvFile .= "4 year institution" . "\n";
                        break;
                    case 2:
                        $csvFile .= "2 year institution" . "\n";
                        break;
                    case 3:
                        $csvFile .= "technical school" . "\n";
                        break;
                    default:
                        $csvFile .= "NR" . "\n";
                }
            }

            //create the zip file
            $namePath = preg_replace("/[^A-Za-z0-9_\-]/", "", $survey1->getDisplayName()) . "-";
            $directory = realpath($request->server->get('DOCUMENT_ROOT')) . $request->server->get('BASE') . "/uploads/" . $namePath;

            $archive = new ZipArchive();
            $zipName = $directory . ".zip";
            $result = $archive->open($zipName, ZipArchive::CREATE);
            if($result !== true){
                $this->addFlash('error', $this->trans("Unable to create a the zip archive"));
                return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_getmatchedsurveys'));
            }
            $archive->addFromString($namePath . ".csv", $csvFile);
            $archive->close();

            $response = new Response(file_get_contents($zipName));
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $namePath . '.zip"');
            $response->headers->set('Content-length', filesize($zipName));

            @unlink($zipName);

            return $response;
        }
        return $this->render('@PaustianPMCIModule/Analysis/pmci_analysis_matchstudent.html.twig', [
                'form' => $form->createView(),]);
    }
}