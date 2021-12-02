<?php

namespace Paustian\PMCIModule\Controller;

use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Symfony\Component\Routing\RouterInterface;
use Paustian\PMCIModule\Entity\MCIDataEntity;
use Symfony\Component\Finder\Exception\AccessDeniedException;

/**
 * @Route("/mcidata")
 */
class MciDataController  extends AbstractController {

    /**
     * @Route("")
     * @param $request - the incoming request.
     * The main entry point
     * @Theme("admin")
     * @return $response The rendered output consisting mainly of the admin menu
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function indexAction(Request $request) {
        //security check
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException($this->trans('You do not have pemission to access the Book admin interface.'));
        }
        // Return a page of menu items.
        $response = new Response($this->render('@PaustianPMCIModule/Response/pmci_response_index.html.twig'));
        return $response;
    }

    /**
     *
     * @Route("/edit/{mciData}")
     *
     * Edit or Delete a person using the MCI. This allows changes to a person
     * @param $request
     * @param $response
     * @return Response The rendered output of the modifyconfig template.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function editAction(Request $request, MCIDataEntity $mciData = null) {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException($this->trans('You do not have pemission to Edit a Person.'));
        }
        return $this->render('@PaustianPMCIModule/Response/pmci_response_index.html.twig');
    }

    /**
     * @Route("/delete/{mciData}")
     * @Theme("admin")
     * @param Request $request
     * @param MCIDataEntity $person
     * @return Response back to the modification screen
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function deleteAction(Request $request, MCIDataEntity $mciData) {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException($this->trans('You do not have pemission to delete a Person.'));
        }
        return $this->render('@PaustianPMCIModule/Response/pmci_response_index.html.twig');
    }



    /**
     * @Route("/modify")
     * @Theme("admin")
     * @param Request $request
     * @return Response
     */
    public function modifyAction(Request $request) {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException($this->trans('You do not have pemission to modify a Person.'));
        }
        return $this->render('@PaustianPMCIModule/Response/pmci_response_index.html.twig');
    }
}