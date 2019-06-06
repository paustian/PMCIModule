<?php

namespace Paustian\PMCIModule;


use Zikula\Core\AbstractExtensionInstaller;
use Paustian\PMCIModule\Entity\PersonEntity;
use Paustian\PMCIModule\Entity\SurveyEntity;
use Paustian\PMCIModule\Entity\MCIDataEntity;

class PMCIModuleInstaller extends AbstractExtensionInstaller {

 
    
    private $entities = array(
            PersonEntity::class,
            SurveyEntity::class,
            MCIDataEntity::class
        );

    /**
     * initialise the pmci module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function install() {
        //Create the tables of the module.
        try {
            $this->schemaTool->create($this->entities);
        } catch (Exception $e) {
            return false;
        }
        $this->_defaultData();

        return true;
    }

    /**
     * upgrade the pmci module from an old version
     * This function can be called multiple times
     */
    public function upgrade($oldversion) {
        //There are no old versions, nothing to upgrade
        // Update successful
        return true;
    }

    /**
     * delete the pmci module data
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function uninstall() {

        //drop the tables
        try {
            $this->schemaTool->drop($this->entities);
        } catch(Exception $e){
            $this->addFlash('error', $e->getMessage());
            return false;
        }
        // Deletion successful
        return true;
    }

    /**
     * put the key in as the first piece of data. This can be called upon to grade  items.
     */
    private function _defaultData(){
        //
        $key = [
            'StudentID' => 0,
            'Q1' => 4,
            'Q2' => 1,
            'Q3' => 2,
            'Q4' => 1,
            'Q5' => 4,
            'Q6' => 2,
            'Q7' => 3,
            'Q8' => 2,
            'Q9' => 4,
            'Q10' => 3,
            'Q11' => 1,
            'Q12' => 3,
            'Q13' => 3,
            'Q14' => 1,
            'Q15' => 4,
            'Q16' => 1,
            'Q17' => 2,
            'Q18' => 1,
            'Q19' => 3,
            'Q20' => 2,
            'Q21' => 4,
            'Q22' => 3,
            'Q23' => 4,
        ];
        $mciData = new MCIDataEntity($key);
        $mciData->setSurveyId(0);
        $mciData->setRespDate(new \DateTime());
        $mciData->setMajor('key');
        $this->entityManager->persist($mciData);
        $this->entityManager->flush();
    }
}

