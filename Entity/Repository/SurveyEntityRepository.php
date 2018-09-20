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
    public function parseCsv($file)
    {
        $csv = file($file->getPathname());
        //if using the old MacOS delimiter, it fails. However we can rescue
        //it by using explode
        if (count($csv) < 2) {
            $csv = explode("\r", $csv[0]);
        }
        //grab the header
        $headerStr = array_shift($csv);
        $header = str_getcsv($headerStr);

        array_walk($csv, [$this, 'csvToArray'], $header);

        //This little loop removes the students IDs present and calculates a hash of them.
        foreach ($csv as $key => $studentData) {
            $studentID = (int)$studentData['StudentID'];
            $studentData['StudentID'] = hexdec(substr(sha1($studentID), 0, 10));
            $csv[$key] = $studentData;
        }
        return $csv;
    }

    private function csvToArray(&$item, $key, $header)
    {
        $item = array_combine($header, str_getcsv($item));
    }

    /**
     * For an array to be valid, it must have the StudentID key and Q1 to Q23
     * this checks for each of those keys
     * @param $cvs
     * @return bool
     */
    public function validateCSV($csv)
    {
        if (!is_array($csv)) {
            return false;
        }
        $firstLine = $csv[0];
        if (!array_key_exists('StudentID', $firstLine)) {
            return 'StudentID is missing';
        }
        for ($i = 1; $i < 23; $i++) {
            $key = 'Q' . $i;
            if (!array_key_exists($key, $firstLine)) {
                return "$key is missing";
            }
        }
        return '';
    }

}