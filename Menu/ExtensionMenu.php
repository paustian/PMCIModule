<?php

declare(strict_types=1);

namespace Paustian\PMCIModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class ExtensionMenu implements ExtensionMenuInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;


    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi
    ) {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }

        if(self::TYPE_USER === $type){
            return $this->getUser();
        }
        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            return null;
        }

        $menu = $this->factory->createItem('pmciMenuMain');

        $menu->addChild(
            'Users', ['uri' => '#',
        ])->setAttribute('icon', 'fas fa-user')
            ->setAttribute('dropdown', true);


        $menu['Users']->addChild('Create MCI users', [
            'route' => 'paustianpmcimodule_person_edit',
        ])->setAttribute('icon', 'fas fa-plus');

        $menu['Users']->addChild('Edit or Delete MCI users', [
            'route' => 'paustianpmcimodule_person_modify',
        ])->setAttribute('icon', 'fas fa-list');

        $menu->addChild(
            'Surveys', ['uri' => '#',
        ])->setAttribute('icon', 'fas fa-poll-h')
            ->setAttribute('dropdown', true);

        $menu['Surveys']->addChild('Edit MCI surveys', [
            'route' => 'paustianpmcimodule_survey_edit',
        ])->setAttribute('icon', 'fas fa-list');

        $menu['Surveys']->addChild('Edit or Delete MCI surveys', [
            'route' => 'paustianpmcimodule_survey_modify',
        ])->setAttribute('icon', 'fas fa-list');

        $menu['Surveys']->addChild('Upload Survey Data', [
            'route' => 'paustianpmcimodule_survey_upload',
        ])->setAttribute('icon', 'fas fa-upload');

        $menu['Surveys']->addChild('Analyze Survey Data', [
            'route' => 'paustianpmcimodule_analysis_index',
        ])->setAttribute('icon', 'fas fa-chart-bar');

        return 0 === $menu->count() ? null : $menu;
    }

    private function getUser() : ?ItemInterface {
        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_DELETE)) {
            return null;
        }

        $menu = $this->factory->createItem('pmciMenuMain');

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Admin Interface', [
                'route' => 'paustianpmcimodule_survey_modify',
            ])->setAttribute('icon', 'fas fa-wrench');
        }

        $menu->addChild('Register to receive the MCI', [
            'route' => 'paustianpmcimodule_person_edit',
        ])->setAttribute('icon', 'fas fa-plus');

        $menu->addChild('Submit your MCI results', [
            'route' => 'paustianpmcimodule_survey_upload',
        ])->setAttribute('icon', 'fas fa-plus');

        $menu->addChild('Analyze MCI results', [
            'route' => 'paustianpmcimodule_analysis_index',
        ])->setAttribute('icon', 'fas fa-plus');

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'PaustianPMCIModule';
    }
}