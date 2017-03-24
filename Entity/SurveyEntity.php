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
     * @ORM\Column(type="date", name="surveyDate")
     * @Assert\Date()
     */
    private $surveyDate;


    
    /**
     * Constructor 
     */
    public function __construct() {

        $this->userId = 0;
        $this->prePost= 0;
        $this->surveyDate=0;
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
     * @return mixed
     */
    public function getSurveyDate()
    {
        return $this->surveyDate;
    }

    /**
     * @param mixed $surveyDate
     */
    public function setSurveyDate($surveyDate)
    {
        $this->surveyDate = $surveyDate;
    }
}


