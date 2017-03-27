<?php


namespace Paustian\PMCIModule\Api;

class AdminApi extends \Zikula_AbstractApi {

    public function getLinks() {
        $links = array();
        
        //The question editing menu
        $submenuLinks = [];
        $submenuLinks[] = [
                'url' => $this->get('router')->generate('paustianpmcimodule_person_edit'),
                'text' => $this->__('Create MCI users')];
        $submenuLinks[] = [
                'url' => $this->get('router')->generate('paustianpmcimodule_person_modify'),
                'text' => $this->__('Edit or Delete MCI users') ];
        $submenuLinks[] = [
            'url' => $this->get('router')->generate('paustianpmcimodule_survey_edit'),
            'text' => $this->__('Edit MCI surveys')];
        $submenuLinks[] = [
            'url' => $this->get('router')->generate('paustianpmcimodule_survey_modify'),
            'text' =>  $this->__('Edit or Delete MCI surveys')];


        $links[] = [
                'url' => $this->get('router')->generate('paustianpmcimodule_survey_edit'),
                'text' => $this->__('Edit MCI surveys'),
                'icon' => 'pencil',
                'links' => $submenuLinks];
       
        return $links;
    }
}

