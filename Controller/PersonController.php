<?php

// ----------------------------------------------------------------------
// Original Author of file: Timothy Paustian
// Purpose of file:  Book administration display functions
// ----------------------------------------------------------------------

namespace Paustian\PMCIModule\Controller;

use Paustian\PMCIModule\Entity\PersonEntity;
use Zikula\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Paustian\PMCIModule\Form\Person;
/**
 * @Route("/person")
 */

class PersonController extends AbstractController {

    /**
     * @Route("")
     * @param $request
     * 
     * @return Response The rendered output consisting mainly of the admin menu
     * 
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function indexAction(Request $request) {
        //security check
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException(__('You do not have pemission to access the PMCI admin interface.'));
        }
        // Return a page of menu items.
        return $this->render('PaustianPMCIModule:Person:pmci_person_menu.html.twig');
    }

    /**
     * 
     * @Route("/edit/{person}")
     * Edit or Delete a person using the MCI. This allows changes to a person
     * @param $request
     * @param $person
     * @return Response The rendered output of the modifyconfig template.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function editAction(Request $request, PersonEntity $person = null) {
        $doMerge = false;
        if (null === $person) {
            if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
                throw new AccessDeniedException($this->__("You do not have permission to edit the persons in the MCI."));
            }
            $person = new PersonEntity();
        } else {
            //to edit a person, you either need to be that user or be an admin
            $currentUserApi = $this->get('zikula_users_module.current_user');
            $uid = $currentUserApi->get('uid');
            $userID = $person->getUserId();
            if ( ($uid != $userID) && (!$this->hasPermission($this->name . '::', $person->getId() . '::', ACCESS_ADD))) {
                throw new AccessDeniedException($this->__("You do not have permission to edit this person's information."));
            }
            $doMerge = true;
        }

        //I need to add the use declaration for this class. 
        $form = $this->createForm(new Person(), $person);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if ($doMerge) {
                $em->merge($person);
            } else {
                $em->persist($person);
            }
            $em->flush();

            $this->addFlash('status', 'Thank you for submitting your request. It will be authorized within 1 business day.');
            return $this->redirect($this->generateUrl('paustianpmcimodule_person_edit'));
        }

        return $this->render('PaustianPMCIModule:Person:pmci_person_edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{person}")
     * @param Request $request
     * @param PersonEntity $person
     *
     * @return Response back to the modification screen
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function deleteAction(Request $request, PersonEntity $person) {
        $response = $this->redirect($this->generateUrl('paustianpmcimodule_admin_editperson'));
        if (null == $person) {
            //you want the edit interface, which has a delete option.
            return $response;
        }
        if (!$this->hasPermission($this->name . '::', $person->getId() . "::", ACCESS_DELETE)) {
            throw new AccessDeniedException($this->__("You do not have permission to delete a person."));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($person);
        $em->flush();
        $this->addFlash('status', __('Person Deleted.'));
        return $response;
    }



    /**
     * @Route("/modify")
     * @param Request $request
     * @return Response
     */
    public function modifyAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $people = $em->getRepository("Paustian\PMCIModule\Entity\PersonEntity")->findAll();
        return $this->render('PaustianPMCIModule:Admin:pmci_admin_modifyperson.html.twig', ['people' => $people]);
    }
}