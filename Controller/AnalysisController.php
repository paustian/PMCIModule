<?php
/**
 * Created by PhpStorm.
 * User: paustian
 * Date: 3/29/17
 * Time: 3:00 PM
 */

namespace Paustian\PMCIModule\Controller;

use Zikula\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove


class AnalysisController extends AbstractController {
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
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_COMMENT)) {
            throw new AccessDeniedException(__('You do not have pemission to access the analysis interface.'));
        }


        // Return a page of menu items.
        $response = new Response($this->render('PaustianPMCIModule:Analysis:pmci_analysis_index.html.twig'));
        return $response;
    }
}