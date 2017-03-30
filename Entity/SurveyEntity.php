<?php
namespace Paustian\PMCIModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PMCISurveyEntity entity class
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="pmci_surveys")
 */
class SurveyEntity extends EntityAccess {

    /**
     * id field (record id)
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="integer", length=20)
     * @Assert\NotBlank()
     */
    private $userId;
    
    /**
     * @ORM\Column(type="integer", length=2)
     * @Assert\NotBlank()
     */
    private $prePost;
    
    /**
     * @ORM\Column(type="date")
     * @Assert\Date()
     */
    public $surveyDate;

    /**
     *
     * institution field (record school)
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $institution;

    /**
     * course
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     *
     */
    private $course;

    /**
     * Constructor 
     */
    public function __construct() {

        $this->userId = 0;
        $this->prePost= 0;
        $this->surveyDate = new \DateTime();
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
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getPrePost()
    {
        return $this->prePost;
    }

    /**
     * @param mixed $prePost
     */
    public function setPrePost($prePost)
    {
        $this->prePost = $prePost;
    }

    /**
     * @return \DateTime
     */
    public function getSurveyDate()
    {
        return $this->surveyDate;
    }

    /**
     * @param \DateTime $surveyDate
     */
    public function setSurveyDate(\DateTime $surveyDate)
    {
        $this->surveyDate = $surveyDate;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param mixed $course
     */
    public function setCourse($course)
    {
        $this->course = $course;
    }


}


