<?php

namespace Paustian\PMCIModule;

use Zikula\Core\ExtensionInstallerInterface;
use Zikula\Core\AbstractBundle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use DoctrineHelper;


class PMCIModuleInstaller implements ExtensionInstallerInterface, ContainerAwareInterface {

 
    
    private $entities = array(
            'Paustian\PMCIModule\Entity\PersonEntity',
            'Paustian\PMCIModule\Entity\SurveyEntity',
            'Paustian\PMCIModule\Entity\MCIDataEntity'
        );
    
    private $entityManager;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var AbstractBundle
     */
    private $bundle;
    /**
     * initialise the book module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function install() {
        // create tables
        $this->entityManager = $this->container->get('doctrine.entitymanager');
        //Create the tables of the module. Book has 5
        try {
            DoctrineHelper::createSchema($this->entityManager, $this->entities);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * upgrade the book module from an old version
     * This function can be called multiple times
     */
    public function upgrade($oldversion) {
        //There are no old versions, nothing to upgrade
        // Update successful
        return true;
    }

    /**
     * delete the book module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function uninstall() {
        // create tables
        $this->entityManager = $this->container->get('doctrine.entitymanager');
        //drop the tables
        DoctrineHelper::dropSchema($this->entityManager, $this->entities);

        // Deletion successful
        return true;
    }
    
    public function setBundle(AbstractBundle $bundle)
    {
        $this->bundle = $bundle;
    }
    
    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->setTranslator($container->get('translator'));
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}

