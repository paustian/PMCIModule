<?php

namespace Paustian\PMCIModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class SurveyEntityRepository extends EntityRepository
{
    /**
     * Grab the csv file and then arrange it into an array of arrays. Each array has the header values
     * as the keys to the array. I need to make sure this doesn't do bad things if poor text is added.
     * see comments for http://php.net/manual/en/function.file.php for an explanation of this code.
     *
     * @param $file
     * @return array
     */
    public function parseCsv($file){
        $fileStr = file($file->getPathname());
       //if using the old MacOS delimiter, it fails. However we can rescue
        //it by using explode
        if(count($fileStr) < 2){
           $fileStr = explode("\r", $fileStr[0]);
       }
        $csv = array_map('str_getcsv', $fileStr);
        array_walk($csv, function(&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv); # remove column header

        //now hash the student IDs so that they are unique and unrecognizable.
        //This is a security measure to make sure that no private info gets out.
        //md5 is good enough for this work, since these are not really passwords
       foreach($csv as $key => $studentData){
           $studentData['StudentID'] = hexdec( substr(sha1($studentData['StudentID']), 0, 15) );
           $csv[$key] = $studentData;
        }
        return $csv;
    }

    /**
     * For an array to be valid, it must have the StudentID key and Q1 to Q23
     * this checks for each of those keys
     * @param $cvs
     * @return bool
     */
    public function validateCSV($csv){
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

}