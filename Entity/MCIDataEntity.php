<?php
namespace Paustian\PMCIModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Response entity class
 *
 * Annotations define the entity mappings to database.
 * @ORM\Entity(repositoryClass="Paustian\PMCIModule\Entity\Repository\MCIDataEntityRepository")
 * @ORM\Table(name="pmci_data")
 */
class MCIDataEntity extends EntityAccess
{
    /**
     * id field (record id)
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * studentId field (record studentId)
     * this is stored as an md5 hash, so it has to be a string
     *
     * @ORM\Column(type="string", length=15)
     */
    private $studentId;

    /**
     * surveyId field (record surveyId)
     * @ORM\Column(type="integer", length=20)
     */
    private $surveyId;

    /**
     * @ORM\Column(type="date", name="respDate")
     * @Assert\Date()
     */
    private $respDate;

    /**
     * q1Resp field (record q1Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q1Resp;

    /**
     * q2Resp field (record q2Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q2Resp;

    /**
     * q3Resp field (record q3Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q3Resp;

    /**
     * q4Resp field (record q4Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q4Resp;

    /**
     * q5Resp field (record q5Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q5Resp;

    /**
     * q6Resp field (record q6Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q6Resp;

    /**
     * q7Resp field (record q7Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q7Resp;

    /**
     * q8Resp field (record q8Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q8Resp;

    /**
     * q9Resp field (record q9Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q9Resp;

    /**
     * q10Resp field (record q10Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q10Resp;

    /**
     * q11Resp field (record q11Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q11Resp;

    /**
     * q12Resp field (record q12Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q12Resp;

    /**
     * q13Resp field (record q13Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q13Resp;

    /**
     * q14Resp field (record q14Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q14Resp;

    /**
     * q15Resp field (record q15Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q15Resp;

    /**
     * q16Resp field (record q16Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q16Resp;

    /**
     * q17Resp field (record q17Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q17Resp;

    /**
     * q18Resp field (record q18Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q18Resp;

    /**
     * q19Resp field (record q19Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q19Resp;

    /**
     * q20Resp field (record q20Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q20Resp;

    /**
     * q21Resp field (record q21Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q21Resp;

    /**
     * q22Resp field (record q22Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q22Resp;

    /**
     * q23esp field (record q23Resp)
     * @ORM\Column(type="integer", length=2)
     */
    private $q23Resp;

    /**
     * gpa
     * @ORM\Column(type="integer", length=4)
     * 1=3.5 and above
     * 2=3.0 - 3.49
     * 3=2.5 - 2.99
     * 4=2.0 - 2.49
     * 5=below 2
     */
    private $gpa;

    /**
     * 
     * race field (record race)
     * @ORM\Column(type="integer", length=2)
     * 1=American Indian/Alaskan Native
     * 2=Black or African American
     * 3=Asian or Pacific Islander
     * 4=Hispanic/Latino
     * 5=White
     * 6=Other or Not reported
     */
    private $race;

    /**
     *
     * age field (record age)
     * @ORM\Column(type="integer", length=4)
     */
    private $age;

    /**
     * major
     * @ORM\Column(type="text")
     */
    private $major;

    /**
     * esl
     * @ORM\Column(type="integer", length=2)
     */
    private $esl;

    /**
     * sex
     * @ORM\Column(type="integer", length=2)
     * 1=male
     * 2=female
     * 3=other
     */
    private $sex;

    /**
     * Constructor 
     */
    public function __construct(array $inData=null)
    {
        if($inData != null){
            $this->studentId = $inData['StudentID'];
            $this->q1Resp = $inData['Q1'];
            $this->q2Resp = $inData['Q2'];
            $this->q3Resp = $inData['Q3'];
            $this->q4Resp = $inData['Q4'];
            $this->q5Resp = $inData['Q5'];
            $this->q6Resp = $inData['Q6'];
            $this->q7Resp = $inData['Q7'];
            $this->q8Resp = $inData['Q8'];
            $this->q9Resp = $inData['Q9'];
            $this->q10Resp = $inData['Q10'];
            $this->q11Resp = $inData['Q11'];
            $this->q12Resp = $inData['Q12'];
            $this->q13Resp = $inData['Q13'];
            $this->q14Resp = $inData['Q14'];
            $this->q15Resp = $inData['Q15'];
            $this->q16Resp = $inData['Q16'];
            $this->q17Resp = $inData['Q17'];
            $this->q18Resp = $inData['Q18'];
            $this->q19Resp = $inData['Q19'];
            $this->q20Resp = $inData['Q20'];
            $this->q21Resp = $inData['Q21'];
            $this->q22Resp = $inData['Q22'];
            $this->q23Resp = $inData['Q23'];
            array_key_exists('Gpa', $inData) ? $this->gpa = $inData['Gpa']: $this->gpa = 0;
            array_key_exists('Sex', $inData) ? $this->sex = $inData['Sex']: $this->sex = 4;
            array_key_exists('Race', $inData) ? $this->race = $inData['Race']: $this->race = 6;
            array_key_exists('Major', $inData) ? $this->major = $inData['Major']: $this->Major = '';
            array_key_exists('Age', $inData) ? $this->age = $inData['Age']: $this->age = 0;
            array_key_exists('Esl', $inData) ? $this->esl = $inData['Esl']: $this->esl = 2;

        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getStudentId()
    {
        return $this->studentId;
    }

    /**
     * @param mixed $studentId
     */
    public function setStudentId($studentId)
    {
        $this->studentId = $studentId;
    }

    /**
     * @return mixed
     */
    public function getSurveyId()
    {
        return $this->surveyId;
    }

    /**
     * @param mixed $surveyId
     */
    public function setSurveyId($surveyId)
    {
        $this->surveyId = $surveyId;
    }

    /**
     * @return \DateTime
     */
    public function getRespDate()
    {
        return $this->respDate;
    }

    /**
     * @param \DateTime $respDate
     */
    public function setRespDate(\DateTime $respDate)
    {
        $this->respDate = $respDate;
    }


    /**
     * @return mixed
     */
    public function getQ1Resp()
    {
        return $this->q1Resp;
    }

    /**
     * @param mixed $q1Resp
     */
    public function setQ1Resp($q1Resp)
    {
        $this->q1Resp = $q1Resp;
    }

    /**
     * @return mixed
     */
    public function getQ2Resp()
    {
        return $this->q2Resp;
    }

    /**
     * @param mixed $q2Resp
     */
    public function setQ2Resp($q2Resp)
    {
        $this->q2Resp = $q2Resp;
    }

    /**
     * @return mixed
     */
    public function getQ3Resp()
    {
        return $this->q3Resp;
    }

    /**
     * @param mixed $q3Resp
     */
    public function setQ3Resp($q3Resp)
    {
        $this->q3Resp = $q3Resp;
    }

    /**
     * @return mixed
     */
    public function getQ4Resp()
    {
        return $this->q4Resp;
    }

    /**
     * @param mixed $q4Resp
     */
    public function setQ4Resp($q4Resp)
    {
        $this->q4Resp = $q4Resp;
    }

    /**
     * @return mixed
     */
    public function getQ5Resp()
    {
        return $this->q5Resp;
    }

    /**
     * @param mixed $q5Resp
     */
    public function setQ5Resp($q5Resp)
    {
        $this->q5Resp = $q5Resp;
    }

    /**
     * @return mixed
     */
    public function getQ6Resp()
    {
        return $this->q6Resp;
    }

    /**
     * @param mixed $q6Resp
     */
    public function setQ6Resp($q6Resp)
    {
        $this->q6Resp = $q6Resp;
    }

    /**
     * @return mixed
     */
    public function getQ7Resp()
    {
        return $this->q7Resp;
    }

    /**
     * @param mixed $q7Resp
     */
    public function setQ7Resp($q7Resp)
    {
        $this->q7Resp = $q7Resp;
    }

    /**
     * @return mixed
     */
    public function getQ8Resp()
    {
        return $this->q8Resp;
    }

    /**
     * @param mixed $q8Resp
     */
    public function setQ8Resp($q8Resp)
    {
        $this->q8Resp = $q8Resp;
    }

    /**
     * @return mixed
     */
    public function getQ9Resp()
    {
        return $this->q9Resp;
    }

    /**
     * @param mixed $q9Resp
     */
    public function setQ9Resp($q9Resp)
    {
        $this->q9Resp = $q9Resp;
    }

    /**
     * @return mixed
     */
    public function getQ10Resp()
    {
        return $this->q10Resp;
    }

    /**
     * @param mixed $q10Resp
     */
    public function setQ10Resp($q10Resp)
    {
        $this->q10Resp = $q10Resp;
    }

    /**
     * @return mixed
     */
    public function getQ11Resp()
    {
        return $this->q11Resp;
    }

    /**
     * @param mixed $q11Resp
     */
    public function setQ11Resp($q11Resp)
    {
        $this->q11Resp = $q11Resp;
    }

    /**
     * @return mixed
     */
    public function getQ12Resp()
    {
        return $this->q12Resp;
    }

    /**
     * @param mixed $q12Resp
     */
    public function setQ12Resp($q12Resp)
    {
        $this->q12Resp = $q12Resp;
    }

    /**
     * @return mixed
     */
    public function getQ13Resp()
    {
        return $this->q13Resp;
    }

    /**
     * @param mixed $q13Resp
     */
    public function setQ13Resp($q13Resp)
    {
        $this->q13Resp = $q13Resp;
    }

    /**
     * @return mixed
     */
    public function getQ14Resp()
    {
        return $this->q14Resp;
    }

    /**
     * @param mixed $q14Resp
     */
    public function setQ14Resp($q14Resp)
    {
        $this->q14Resp = $q14Resp;
    }

    /**
     * @return mixed
     */
    public function getQ15Resp()
    {
        return $this->q15Resp;
    }

    /**
     * @param mixed $q15Resp
     */
    public function setQ15Resp($q15Resp)
    {
        $this->q15Resp = $q15Resp;
    }

    /**
     * @return mixed
     */
    public function getQ16Resp()
    {
        return $this->q16Resp;
    }

    /**
     * @param mixed $q16Resp
     */
    public function setQ16Resp($q16Resp)
    {
        $this->q16Resp = $q16Resp;
    }

    /**
     * @return mixed
     */
    public function getQ17Resp()
    {
        return $this->q17Resp;
    }

    /**
     * @param mixed $q17Resp
     */
    public function setQ17Resp($q17Resp)
    {
        $this->q17Resp = $q17Resp;
    }

    /**
     * @return mixed
     */
    public function getQ18Resp()
    {
        return $this->q18Resp;
    }

    /**
     * @param mixed $q18Resp
     */
    public function setQ18Resp($q18Resp)
    {
        $this->q18Resp = $q18Resp;
    }

    /**
     * @return mixed
     */
    public function getQ19Resp()
    {
        return $this->q19Resp;
    }

    /**
     * @param mixed $q19Resp
     */
    public function setQ19Resp($q19Resp)
    {
        $this->q19Resp = $q19Resp;
    }

    /**
     * @return mixed
     */
    public function getQ20Resp()
    {
        return $this->q20Resp;
    }

    /**
     * @param mixed $q20Resp
     */
    public function setQ20Resp($q20Resp)
    {
        $this->q20Resp = $q20Resp;
    }

    /**
     * @return mixed
     */
    public function getQ21Resp()
    {
        return $this->q21Resp;
    }

    /**
     * @param mixed $q21Resp
     */
    public function setQ21Resp($q21Resp)
    {
        $this->q21Resp = $q21Resp;
    }

    /**
     * @return mixed
     */
    public function getQ22Resp()
    {
        return $this->q22Resp;
    }

    /**
     * @param mixed $q22Resp
     */
    public function setQ22Resp($q22Resp)
    {
        $this->q22Resp = $q22Resp;
    }

    /**
     * @return mixed
     */
    public function getQ23Resp()
    {
        return $this->q23Resp;
    }

    /**
     * @param mixed $q23Resp
     */
    public function setQ23Resp($q23Resp)
    {
        $this->q23Resp = $q23Resp;
    }

    /**
     * @return mixed
     */
    public function getGpa()
    {
        return $this->gpa;
    }

    /**
     * @param mixed $gpa
     */
    public function setGpa($gpa)
    {
        $this->gpa = $gpa;
    }

    /**
     * @return mixed
     */
    public function getRace()
    {
        return $this->race;
    }

    /**
     * @param mixed $race
     */
    public function setRace($race)
    {
        $this->race = $race;
    }

    /**
     * @return mixed
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param mixed $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * @return mixed
     */
    public function getSchool()
    {
        return $this->school;
    }

    /**
     * @param mixed $school
     */
    public function setSchool($school)
    {
        $this->school = $school;
    }

    /**
     * @return mixed
     */
    public function getMajor()
    {
        return $this->major;
    }

    /**
     * @param mixed $major
     */
    public function setMajor($major)
    {
        $this->major = $major;
    }

    /**
     * @return mixed
    */
    public function getEsl()
    {
        return $this->esl;
    }

    /**
     * @param mixed $isl
     */
    public function setEsl($esl)
    {
        $this->esl = $esl;
    }

    /**
     * @return mixed
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @param mixed $sex
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
    }


}


