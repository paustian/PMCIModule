<?php
/**
 * Created by PhpStorm.
 * User: paustian
 * Date: 3/30/17
 * Time: 12:36 PM
 */

namespace Paustian\PMCIModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class MCIDataEntityRepository extends EntityRepository
{


    public function matchStudents($MCI1, $MCI2){

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
            $q = 'q' . $i . 'Resp';
            if($student[$q] == $key[$q]){
                $score++;
            }
        }
        return ($score *100)/23;
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
    public function gradeItem($responseArray, $item, $answer){
        $score = 0;
        $total = count($responseArray);
        $q = 'q' . $item . 'Resp';
        foreach($responseArray as $response){
            if($response[$q] == $answer){
                $score++;
            }
        }
        return ($score * 100)/$total;
    }

    /**
     * Given an array of scores to compare in this format
     *
     * $pre = studentId => score
     * $post = studentID => score
     *
     * Calculate learning gains for each student. This function assumes you are
     * sending matched arrays that contain each student ID. They should be the same
     * size and should also contain the same keys
     *
     * @param $preArray
     * @param $postArray
     * @return array|bool
     */
    public function calcStudentNLearningGain($preArray, $postArray){
        //I don't want to blow things up here. That is for the calling function to decide
        //However the arrays have to be matched for this to work.
        if(!$this->studentIdMatch($preArray, $postArray)){
            return false;
        }
        $LGScores = [];
        foreach($postArray as $key => $post){
            $LG = ($post - $preArray[$key])/(100 - $post);
            $LGScores[$key] = $LG;
        }
        return $LGScores;
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