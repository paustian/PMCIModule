<?php
namespace Paustian\PMCIModule;

use Zikula\SearchModule\AbstractSearchable;

class PMCIModuleVersion extends \Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['name'] = __('PMCI');
        $meta['version'] = '1.0.0';
        $meta['displayname'] = __('PMCI');
        $meta['description'] = __('A module for asking for the Microbiology Concept Inventory and for submitting responses for data analysis.');
        // this defines the module's url and should be in lowercase without space
        $meta['url'] = $this->__('pmci');
        $meta['core_min'] = '1.4.0'; // Fixed to 1.3.x range
        $meta['capabilities'] = array(AbstractSearchable::SEARCHABLE => array('class' => 'Paustian\PMCIModule\Helper\SearchHelper'));
        $meta['securityschema'] = array('PaustianPMCIModule::' => 'Person::Survey');
        $meta['author'] = 'Timothy Paustian';
        $meta['contact'] = 'http://http://www.bact.wisc.edu/faculty/paustian/';
        
        return $meta;
    }
}
