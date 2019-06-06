<?php

/*
 * This file is part of the Formicula package.
 *
 * Copyright Formicula Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Paustian\PMCIModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\PermissionsModule\Api\PermissionApi;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * LinkContainer constructor.
     *
     * @param TranslatorInterface $translator    TranslatorInterface service instance
     * @param RouterInterface     $router        RouterInterface service instance
     * @param PermissionApi       $permissionApi PermissionApi service instance
     */
    public function __construct(TranslatorInterface $translator, RouterInterface $router, PermissionApi $permissionApi)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
    }

    /**
     * get Links of any type for this extension
     * required by the interface
     *
     * @param string $type
     * @return array
     */
    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        if (LinkContainerInterface::TYPE_ADMIN == $type) {
            return $this->getAdmin();
        }
        if (LinkContainerInterface::TYPE_USER == $type) {
            return $this->getUser();
        }
        return [];
    }

    /**
     * get the Admin links for this extension
     *
     * @return array
     */
    private function getAdmin()
    {
        $links = [];

        if (!$this->permissionApi->hasPermission('PaustianPMCIModule::', '::', ACCESS_ADMIN)) {
            return $links;
        }

        $links[] = [
            'url' => $this->router->generate('paustianpmcimodule_person_edit'),
            'text' => $this->translator->__('Edit Person'),
            'icon' => 'group'
        ];
        $links[] = [
            'url' => $this->router->generate('paustianpmcimodule_person_modify'),
            'text' => $this->translator->__('Modify Person'),
            'icon' => 'user-plus'
        ];
        $links[] = [
            'url' => $this->router->generate('paustianpmcimodule_survey_edit'),
            'text' => $this->translator->__('Edit MCI Survey'),
            'icon' => 'wrench'
        ];

        return $links;
    }

    private function getUser()
    {
        $links = array();

        $submenuLinks = [];
        $submenuLinks[] = [
            'url' => $this->router->generate('paustianpmcimodule_person_edit'),
            'text' => $this->translator->__('Register to recieve the MCI')];
        $submenuLinks[] = [
            'url' => $this->router->generate('paustianpmcimodule_survey_upload'),
            'text' => $this->translator->__('Submit your MCI results')];
        $submenuLinks[] = [
            'url' => $this->router->generate('paustianpmcimodule_analysis_index'),
            'text' => $this->translator->__('Analyze MCI results')];

        $links[] = [
            'url' => $this->router->generate('paustianpmcimodule_person_edit'),
            'text' => $this->translator->__('User Requests'), //$this->translator->__('Get the MCI'),
            'icon' => 'pencil',
            'links' => $submenuLinks];

        return $links;
    }
    /**
     * set the BundleName as required by the interface
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'PaustianPMCIModule';
    }
}
