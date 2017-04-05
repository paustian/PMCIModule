<?php
/**
 * Created by PhpStorm.
 * User: paustian
 * Date: 3/30/17
 * Time: 12:36 PM
 */

namespace Paustian\PMCIModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\PMCIModule\Entity\MCIDataEntity;
use Paustian\PMCIModule\Entity\SurveyEntity;

class MCIDataEntityRepository extends EntityRepository
{

    /**
     * Give two surveys, find all the students who took both and then put them in an array.
     * The array structure is:
     * $matchedStudents [studentId]
     *                  [0] - firstSurvey responses
     *                  [1] - secondSurvey responses
     * @param $survey1
     * @param $survey2
     * $param $options - options for the search criteria
     * @return array
     */
    public function matchStudents(SurveyEntity $survey1, SurveyEntity $survey2, $options = [])
    {
        //build the critieria
        $criteria1 = $this->_createCriteria($survey1, $options);
        $criteria2 = $this->_createCriteria($survey2, $options);

        $mci1Data = $this->matching($criteria1);

        $matchedStudents = [];
        foreach ($mci1Data as $stud1Survey) {
            $studentId = $stud1Survey->getStudentId();
            $criteria3 = clone($criteria2);
            $criteria3->andWhere($criteria2->expr()->eq('studentId', $studentId));
            $mci2Data = $this->matching($criteria3);
            if (!$mci2Data->isEmpty()) {
                $matchedStudents[$studentId]['pre'] = $stud1Survey;
                $stud2Survey = $mci2Data->first();
                $matchedStudents[$studentId]['post'] = $stud2Survey;
            }
        }
        return $matchedStudents;
    }

    private function _createCriteria(SurveyEntity $survey, $options)
    {
        $surveyId = $survey->getId();
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria->where($criteria->expr()->eq('surveyId', $surveyId));
        foreach ($options as $opt) {
            if ($opt[0] == '=') {
                $criteria->andWhere($criteria->expr()->eq($opt[1], $opt[2]));
            }
            if ($opt[0] == '<') {
                $criteria->andWhere($criteria->expr()->lt($opt[1], $opt[2]));
            }
            if ($opt[0] == '>') {
                $criteria->andWhere($criteria->expr()->gt($opt[1], $opt[2]));
            }
        }
        return $criteria;
    }

    /**
     * grade each matched student and then return the average on the pre and post tests
     * @param $matchedStudents
     * @param $answers
     * @return array
     */
    public function gradeMatchedStudents(&$matchedStudents, $answers)
    {
        $preTotal = 0;
        $postTotal = 0;
        $count = count($matchedStudents);
        foreach ($matchedStudents as $key => $student) {
            //grab the student first score
            $studArray1 = $student['pre']->toArray();
            $preGrade = $this->gradeStudent($studArray1, $answers);
            $matchedStudents[$key]['preScore'] = $preGrade;
            $preTotal += $preGrade;
            $studArray2 = $student['post']->toArray();
            $postGrade = $this->gradeStudent($studArray2, $answers);
            $matchedStudents[$key]['postScore'] = $postGrade;
            $postTotal += $postGrade;
        }
        $retArray = ['preAvg' => $preTotal / $count, 'postAvg' => $postTotal / $count];
        return $retArray;
    }

    /**
     * Given a students responses to the MCI. Determine the percent correct.
     *
     * @param $student
     * @param $key
     * @return float|int
     */
    public function gradeStudent($student, $key)
    {
        $score = 0;

        for ($i = 1; $i < 24; $i++) {
            //getQ1Resp
            $q = 'q' . $i . 'Resp';
            if ($student[$q] == $key[$q]) {
                $score++;
            }
        }
        return ($score * 100) / 23;
    }

    public function calculateItemLearningGains(SurveyEntity $survey1, SurveyEntity $survey2, $answers, $options = [])
    {
        //create criteria
        $criteria1 = $this->_createCriteria($survey1, $options);
        $criteria2 = $this->_createCriteria($survey2, $options);

        $mci1Data = $this->matching($criteria1);
        $mci2Data = $this->matching($criteria2);

        //zero out the score keeping arrays
        $testResults1 = [];
        $testResults2 = [];
        for ($i = 1; $i < 24; $i++) {
            $testResults1[$i] = 0;
            $testResults2[$i] = 0;
        }
        //calculate the proportion that got each question right
        foreach ($mci1Data as $student) {
            $this->gradeItem($student, $answers, $testResults1);
        }
        foreach ($mci2Data as $student) {
            $this->gradeItem($student, $answers, $testResults2);
        }
        //normalize score to the number of students. This is the
        //proportion correct. Also calculate the learning gain.
        $itemResults = [];
        $studentNumberS1 = count($mci1Data);
        $studentNumberS2 = count($mci2Data);
        for ($i = 1; $i < 24; $i++) {
            $itemResults[$i]['pre'] = round(($testResults1[$i] * 100) / $studentNumberS1, 2);
            $itemResults[$i]['post'] = round(($testResults2[$i] * 100) / $studentNumberS2, 2);
            $itemResults[$i]['lg'] = $this->_calcLearningGain($itemResults[$i]['pre'], $itemResults[$i]['post']);
        }
        return $itemResults;
    }

    /**
     * Given a student response array to an item, determine
     * how many answered it correctly.
     *
     * @param $responseArray
     * @param $item
     * @param $answer
     * @return float|int
     */
    public function gradeItem(MCIDataEntity $survey, $answers, &$testResults)
    {
        for ($i = 1; $i < 24; $i++) {
            $q1 = 'getQ' . $i . 'Resp';
            $q2 = 'q' . $i . 'Resp';
            if ($survey->$q1() == $answers[$q2]) {
                $testResults[$i]++;
            }
        }
    }

    /**
     * Calculate the learning gain for given an preScore and a postScore.
     *
     * @param $preScore
     * @param $postScore
     * @return float|int
     */
    private function _calcLearningGain($preScore, $postScore)
    {
        $lg = ($postScore - $preScore) / (100 - $preScore);
        return round($lg, 3);
    }

    /**
     * Given an array of scores to compare in this format
     *
     * You submit an array of matched students and then calculate their learning gains
     * We assume matchStudents has been called above and that each students has been graded
     *
     *
     * Calculate normalized learning gains for each student. This function assumes you are
     * sending an array that has been processed by matchStudents
     * size and should also contain the same keys
     *
     * @param $preArray
     * @param $postArray
     * @return float|bool
     */
    public function calcStudentLearningGains(&$matchedStudents)
    {
        $count = count($matchedStudents);
        $total_lg = 0;
        foreach ($matchedStudents as $key => $scores) {
            if (!array_key_exists('preScore', $scores) ||
                !array_key_exists('postScore', $scores)
            ) {
                return false;
            }
            $LG = $this->_calcLearningGain($scores['preScore'], $scores['postScore']);
            $matchedStudents[$key]['lg'] = $LG;
            $total_lg += $LG;
        }
        return (float)($total_lg / $count);
    }

    /**
     * Given an array of scores to compare in this format
     *
     * $arr1 = studentId => score
     * $arr2 = studentId => score
     *
     * make sure that $arr2 has every ID that $arr1 has. You will need to call this in reverse
     * to make sure $arr1 has ever value that $arr2 has.
     *
     * @param $arr1
     * @param $arr2
     * @return bool
     */
    public function studentIdMatch($arr1, $arr2)
    {
        $return = false;
        //if the arrays are not the same size, then by definition
        //they cannot contain the same student IDs
        if (count($arr1) != count($arr2)) {
            return $return;
        }
        //now walk the arrays and make sure they contain the
        //same student IDs
        if (!empty($arr1) && !empty($arr2)) {
            foreach ($arr1 as $key => $value) {
                $return = array_key_exists($key, $arr2);
                if ($return == false) {
                    break;
                }
            }
        }
        return $return;
    }

    public function calculateItemDiscr($survey1, $survey2, $options)
    {
        $key = $this->getKey();
        return true;
    }

    public function getKey()
    {
        //the first entry in the table should be the key
        $keyEntity = $this->findOneBy(['id' => 1, 'studentId' => 0, 'surveyId' => 0]);
        return $keyEntity->toArray();
    }

    /**
     * Given a survey with options for who to include, calculate the point biserial correlation for each test item.
     *
     * @param $survey
     * @param $options
     * @param $key
     * @return array
     */
    public function calculatePbc($survey, $options)
    {
        //create criteria
        $criteria = $this->_createCriteria($survey, $options);
        //grad the matching survey data
        $mciData = $this->matching($criteria);
        //get the key
        $key = $this->getKey();
        $scoreArray = [];
        //grade the survey so that we can calculate the standard deviation
        foreach ($mciData as $student) {
            $scoreArray[] = $this->gradeStudent($student, $key);
        }
        //determine the standard deviation
        $sd = $this->standard_deviation($scoreArray);
        $numStudents = count($mciData);
        $pbcArray = [];
        //walk through each question and determine the pbc
        for ($i = 1; $i < 24; $i++) {
            $correctStud = [];
            $incorrectStud = [];
            $numCorrect = 0;
            $numIncorrect = 0;
            $q1 = 'getQ' . $i . 'Resp';
            $q2 = 'q' . $i . 'Resp';
            //sort into correct and incorrect arrays
            foreach ($mciData as $student) {
                if ($student->$q1() == $key[$q2]) {
                    $correctStud[$student->getStudentId()] = $student;
                    $numCorrect++;
                } else {
                    $incorrectStud[$student->getStudentId()] = $student;
                    $numIncorrect++;
                }
            }
            $M1 = $this->_calcMean($correctStud, $key);
            $M0 = $this->_calcMean($incorrectStud, $key);
            $p = $numCorrect / $numStudents;
            $q = $numIncorrect / $numStudents;

            $pbcArray[$i] = round((($M1 - $M0) / $sd) * sqrt($p * $q), 4);
        }
        return $pbcArray;
    }

    private function _calcMean($studArray, $key)
    {
        $scoreArray = [];
        foreach ($studArray as $student) {
            $scoreArray[] = $this->gradeStudent($student, $key);
        }
        return array_sum($scoreArray) / count($scoreArray);
    }

    public function standard_deviation($aValues, $bSample = false)
    {
        if(!is_array($aValues)){
            return false;
        }
        $fMean = array_sum($aValues) / count($aValues);
        $fVariance = 0.0;
        foreach ($aValues as $i)
        {
            $fVariance += pow($i - $fMean, 2);
        }
        $fVariance /= ( $bSample ? count($aValues) - 1 : count($aValues) );
        return (float) sqrt($fVariance);
    }

    public function calcItemDiscrimination($survey, $options){
        //create criteria
        $criteria = $this->_createCriteria($survey, $options);
        //grad the matching survey data
        $mciData = $this->matching($criteria);
        //get the key
        $key = $this->getKey();
        $scoreArray = [];
        //grade the survey so that we can calculate the standard deviation
        foreach ($mciData as $student) {
            $scoreArray[] = ['score' => $this->gradeItem($student, $key), 'student' => $student];
        }
        //The score array now has all our score. Sort it.
        usort($scoreArray, $this->_sortFunc);
        $cutOff27 = 0.27 * count($scoreArray);

        $topStudents = array_slice($scoreArray, $cutOff27);
        $bottomStudents = array_slice($scoreArray, -$cutOff27);


    }

    private function _sortFunc($a, $b){
        if($a['score'] == $b['score']){
            return 0;
        }
        return ($a['score'] < $b['score']) ? -1 : 1;
    }

}