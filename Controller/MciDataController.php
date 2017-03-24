<?php
/**
 * Created by PhpStorm.
 * User: paustian
 * Date: 3/17/17
 * Time: 4:33 PM
 */

namespace Paustian\PMCIModule\Controller;

use Zikula\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Routing\RouterInterface;
use Paustian\PMCIModule\Entity\MCIDataEntity;

/**
 * @Route("/mcidata")
 */
class MciDataController  extends AbstractController {

    /**
     * @Route("")
     * @param $request - the incoming request.
     * The main entry point
     *
     * @return $response The rendered output consisting mainly of the admin menu
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function indexAction(Request $request) {
        //security check
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException(__('You do not have pemission to access the Book admin interface.'));
        }
        // Return a page of menu items.
        $response = new Response($this->render('PaustianPMCIModule:Response:pmci_response_index.html.twig'));
        return $response;
    }

    /**
     *
     * @Route("/edit/{mciData}")
     * Edit or Delete a person using the MCI. This allows changes to a person
     * @param $request
     * @param $response
     * @return Response The rendered output of the modifyconfig template.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function editAction(Request $request, MCIDataEntity $mciData = null) {
        $doMerge = false;

        return $this->render('PaustianPMCIModule:Response:pmci_response_index.html.twig');
    }

    /**
     * @Route("/delete/{mciData}")
     * @param Request $request
     * @param MCIDataEntity $person
     * @return Response back to the modification screen
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function deleteAction(Request $request, MCIDataEntity $mciData) {
        return $this->render('PaustianPMCIModule:Response:pmci_response_index.html.twig');
    }



    /**
     * @Route("/modify")
     * @param Request $request
     * @return Response
     */
    public function modifyAction(Request $request) {
        return $this->render('PaustianPMCIModule:Response:pmci_response_index.html.twig');
    }
}