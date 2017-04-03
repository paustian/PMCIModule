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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Paustian\PMCIModule\Form\Person;
use Swift_Message;
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
        return $this->editAction($request);
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
        $currentUserApi = $this->get('zikula_users_module.current_user');
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$currentUserApi->isLoggedIn()) {
            $this->addFlash('error', $this->__('You need to register as a user before you can obtain the MCI.'));
            return $this->redirect($this->generateUrl('zikulausersmodule_registration_register'));
        }
        $uid = $currentUserApi->get('uid');

        if (null === $person) {
            if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
                throw new AccessDeniedException($this->__("You do not have permission to edit the persons in the MCI."));
            }
            $person = new PersonEntity();
            $person->setUserId($uid);
            $person->setEmail($currentUserApi->get('email'));
            $person->setName($currentUserApi->get('uname'));
        } else {
            //to edit a person, you either need to be that user or be an admin
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
            $formData = $form->getData();

            //Now notify the admin that someone wants the MCI
            $message = Swift_Message::newInstance();
            $variableApi = $this->get('zikula_extensions_module.api.variable');
            $mailer = $this->get('zikula_mailer_module.api.mailer');
            $adminMail = $variableApi->getSystemVar('adminmail');
            $siteName = $variableApi->getSystemVar('sitename');
            $message->setFrom([$formData['email'] => $formData['name']]);
            $message->setTo([$adminMail => $siteName]);
            $msgBody = $formData['name'] . __('of') . $formData['institution'] .  __('has requested a copy of the MCI. Please email it to the address:' . $formData['email']);

            $result = $mailer->sendMessage($message, 'A request for the MCI', $msgBody);
            if(!$result) {
                $this->addFlash('status', 'Your messaged failed, please check your email address and name.');
            } else {
                $this->addFlash('status', 'Thank you for submitting your request. It will be authorized within 1 business day.');
                //now save the data back to the
                $em = $this->getDoctrine()->getManager();
                if ($doMerge) {
                    $em->merge($person);
                } else {
                    $em->persist($person);
                }
                $em->flush();
            }
            return $this->redirect($this->generateUrl('paustianpmcimodule_person_edit'));
        }

        return $this->render('PaustianPMCIModule:Person:pmci_person_edit.html.twig', [
                    'form' => $form->createView(),]
        );
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
        $response = $this->redirect($this->generateUrl('paustianpmcimodule_person_edit'));
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
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_EDIT)) {
            throw new AccessDeniedException($this->__("You do not have permission to edit a person."));
        }
        $em = $this->getDoctrine()->getManager();
        $people = $em->getRepository("Paustian\PMCIModule\Entity\PersonEntity")->findAll();
        return $this->render('PaustianPMCIModule:Person:pmci_person_modifyperson.html.twig', ['people' => $people]);
    }
}