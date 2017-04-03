<?php
namespace Paustian\PMCIModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * PersonEntity entity class
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity(repositoryClass="Paustian\PMCIModule\Entity\Repository\PersonEntityRepository")
 * @ORM\Table(name="pmci_persons")
 */
class PersonEntity extends EntityAccess {

    /**
     * id field (record id)
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * userId field (record userId)
     * @ORM\Column(type="integer", length=20)
     */
    private $userId;

    /**
     * name
     * 
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $name = '';

    /**
     * email
     *
     * @ORM\Column(type="text")
     *  @Assert\NotBlank()
     */
    private $email;

    /**
     * institution
     * @ORM\Column(type="text")
     *  @Assert\NotBlank()
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
        $this->name = '';
        $this->email = '';
        $this->institution = '';
        $this->course='';
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @return mixed
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @param mixed $course
     */
    public function setCourse($course)
    {
        $this->course = $course;
    }
}


