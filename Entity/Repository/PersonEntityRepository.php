<?php

namespace Paustian\PMCIModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class PersonEntityRepository extends EntityRepository
{
    public function getCurrentPerson($currentUserApi){
        $uid = $currentUserApi->get('uid');
        $person = $this->_em->getRepository('Paustian\PMCIModule\Entity\PersonEntity')->findOneBy(['userId' => $uid]);
        return $person;
    }
}