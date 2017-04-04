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

    public function getKey(){
        //the first entry in the table should be the key
        $keyEntity = $this->findOneBy(['id' => 1, 'studentId' => 0, 'surveyId' => 0]);
        return $keyEntity->toArray();
    }
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
    public function matchStudents(SurveyEntity $survey1, SurveyEntity $survey2, $options=[]){
        $mci1Id = $survey1->getId();
        $mci2Id = $survey2->getId();

        $criteria1 = new \Doctrine\Common\Collections\Criteria();
        $criteria2 = new \Doctrine\Common\Collections\Criteria();
        //build the first criteria
        $criteria1->where($criteria1->expr()->eq('surveyId', $mci1Id));
        foreach($options as $opt){
            if($opt[0] == '='){
                $criteria1->andWhere($criteria1->expr()->eq($opt[1], $opt[2]));
            }
            if($opt[0] == '<'){
                $criteria1->andWhere($criteria1->expr()->lt($opt[1], $opt[2]));
            }
            if($opt[0] == '>'){
                $criteria1->andWhere($criteria1->expr()->gt($opt[1], $opt[2]));
            }
        }

        $criteria2->where($criteria2->expr()->eq('surveyId', $mci2Id));
        foreach($options as $opt){
            if($opt[0] == '='){
                $criteria2->andWhere($criteria2->expr()->eq($opt[1], $opt[2]));
            }
            if($opt[0] == '<'){
                $criteria2->andWhere($criteria2->expr()->lt($opt[1], $opt[2]));
            }
            if($opt[0] == '>'){
                $criteria2->andWhere($criteria2->expr()->gt($opt[1], $opt[2]));
            }
        }
        $mci1Data = $this->matching($criteria1);

        $matchedStudents = [];
        foreach($mci1Data as $stud1Survey){
            $studentId = $stud1Survey->getStudentId();
            $criteria3 = clone($criteria2);
            $criteria3->andWhere($criteria2->expr()->eq('studentId',  $studentId));
            $mci2Data = $this->matching($criteria3);
            if(!$mci2Data->isEmpty()){
                $matchedStudents[$studentId]['pre'] = $stud1Survey;
                $stud2Survey = $mci2Data->first();
                $matchedStudents[$studentId]['post'] = $stud2Survey;
            }
        }
        return $matchedStudents;
    }

    /**
     * Given a students responses to the MCI. Determine the percent correct.
     *
     * @param $student
     * @param $key
     * @return float|int
     */
    public function gradeStudent($student, $key){
        $score = 0;

        for($i=1; $i<24; $i++){
            //getQ1Resp
            $q = 'q' . $i . 'Resp';
            if($student[$q] == $key[$q]){
                $score++;
            }
        }
        return ($score *100)/23;
    }

    /**
     * grade each matched student and then return the average on the pre and post tests
     * @param $matchedStudents
     * @param $answers
     * @return array
     */
    public function gradeMatchedStudents(&$matchedStudents, $answers){
        $preTotal = 0;
        $postTotal = 0;
        $count = count($matchedStudents);
        foreach($matchedStudents as $key => $student){
            //grab the student first score
            $studArray1 = $student['pre']->toArray();
            $preGrade = $this->gradeStudent($studArray1, $answers);
            $matchedStudents[$key]['preScore'] =$preGrade;
            $preTotal += $preGrade;
            $studArray2 = $student['post']->toArray();
            $postGrade = $this->gradeStudent($studArray2, $answers);
            $matchedStudents[$key]['postScore'] = $postGrade;
            $postTotal += $postGrade;
        }
        $retArray = ['preAvg' => $preTotal/$count, 'postAvg' => $postTotal/$count];
        return $retArray;
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
    public function gradeItem(MCIDataEntity $survey, $answers, &$testResults){
        for($i = 1; $i < 24; $i++){
            $q1 = 'getQ' . $i . 'Resp';
            $q2 = 'q' . $i . 'Resp';
            if($survey->$q1() == $answers[$q2]){
                $testResults[$i]++;
            }
        }
    }

    public function calculateItemLearningGains(SurveyEntity $survey1, SurveyEntity $survey2, $answers, $options=[]){
        $mci1Id = $survey1->getId();
        $mci2Id = $survey2->getId();
        $criteria1 = new \Doctrine\Common\Collections\Criteria();
        $criteria2 = new \Doctrine\Common\Collections\Criteria();
        //build the first criteria
        $criteria1->where($criteria1->expr()->eq('surveyId', $mci1Id));
        foreach($options as $opt){
            if($opt[0] == '='){
                $criteria1->andWhere($criteria1->expr()->eq($opt[1], $opt[2]));
            }
            if($opt[0] == '<'){
                $criteria1->andWhere($criteria1->expr()->lt($opt[1], $opt[2]));
            }
            if($opt[0] == '>'){
                $criteria1->andWhere($criteria1->expr()->gt($opt[1], $opt[2]));
            }
        }

        $criteria2->where($criteria2->expr()->eq('surveyId', $mci2Id));
        foreach($options as $opt){
            if($opt[0] == '='){
                $criteria2->andWhere($criteria2->expr()->eq($opt[1], $opt[2]));
            }
            if($opt[0] == '<'){
                $criteria2->andWhere($criteria2->expr()->lt($opt[1], $opt[2]));
            }
            if($opt[0] == '>'){
                $criteria2->andWhere($criteria2->expr()->gt($opt[1], $opt[2]));
            }
        }
        $mci1Data = $this->matching($criteria1);
        $mci2Data = $this->matching($criteria2);

        //zero out the score keeping arrays
        $testResults1 = [];
        $testResults2 = [];
        for($i = 1; $i < 24; $i++){
            $testResults1[$i] = 0;
            $testResults2[$i] = 0;
        }
        //calculate the proportion that got each question right
        foreach($mci1Data as $student){
            $this->gradeItem($student, $answers, $testResults1);
        }
        foreach($mci2Data as $student){
            $this->gradeItem($student, $answers, $testResults2);
        }
        //normalize score to the number of students. This is the
        //proportion correct. Also calculate the learning gain.
        $itemResults = [];
        $studentNumberS1 = count($mci1Data);
        $studentNumberS2 = count($mci2Data);
        for($i = 1; $i < 24; $i++){
            $itemResults[$i]['pre'] = round(($testResults1[$i] * 100)/$studentNumberS1, 2);
            $itemResults[$i]['post'] = round(($testResults2[$i] * 100)/$studentNumberS2, 2);
            $itemResults[$i]['lg'] = $this->_calcLearningGain($itemResults[$i]['pre'], $itemResults[$i]['post'] );
        }
        return $itemResults;
    }

    /**
     * Calculate the learning gain for given an preScore and a postScore.
     *
     * @param $preScore
     * @param $postScore
     * @return float|int
     */
    private function _calcLearningGain($preScore, $postScore){
        $lg = ($postScore - $preScore)/(100 - $preScore);
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
    public function calcStudentLearningGains(&$matchedStudents){
        $count = count($matchedStudents);
        $total_lg=0;
        foreach($matchedStudents as $key => $scores){
            if(!array_key_exists('preScore', $scores) ||
                !array_key_exists('postScore', $scores)){
                return false;
            }
            $LG = $this->_calcLearningGain($scores['preScore'], $scores['postScore']);
            $matchedStudents[$key]['lg'] = $LG;
            $total_lg+=$LG;
        }
        return (float)($total_lg/$count);
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
    public function studentIdMatch($arr1, $arr2){
        $return = false;
        //if the arrays are not the same size, then by definition
        //they cannot contain the same student IDs
        if(count($arr1) != count($arr2)){
            return $return;
        }
        //now walk the arrays and make sure they contain the
        //same student IDs
        if(!empty($arr1) && !empty($arr2)){
            foreach($arr1 as $key => $value){
                $return = array_key_exists($key, $arr2);
                if($return == false){
                    break;
                }
            }
        }
        return $return;
    }
}