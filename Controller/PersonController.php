<?php

// ----------------------------------------------------------------------
// Original Author of file: Timothy Paustian
// Purpose of file:  Book administration display functions
// ----------------------------------------------------------------------

namespace Paustian\PMCIModule\Controller;

use Paustian\PMCIModule\Entity\PersonEntity;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Symfony\Component\Routing\RouterInterface;
use Paustian\PMCIModule\Form\Person;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Zikula\UsersModule\Api\CurrentUserApi;

/**
 * @Route("/person")
 */

class PersonController extends AbstractController {

    /**
     * @Route("")
     * @param $request
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
     * @param Request $request
     * @param MailerInterface $mailer
     * @param VariableApiInterface $variableApi
     * @param CurrentUserApi $currentUserApi
     * @param PersonEntity|null $person
     * @return Response The rendered output of the modifyconfig template.
     */
    public function editAction(Request $request,
                               MailerInterface $mailer,
                               VariableApiInterface $variableApi,
                               CurrentUserApi $currentUserApi,
                               PersonEntity $person = null) {
        $doMerge = false;
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$currentUserApi->isLoggedIn()) {
            $this->addFlash('error', $this->trans('You need to register as a user before you can obtain the MCI.'));
            return $this->redirect($this->generateUrl('zikulausersmodule_registration_register'));
        }
        $uid = $currentUserApi->get('uid');

        if (null === $person) {
            if (!$this->hasPermission($this->name . '::', '::', ACCESS_COMMENT)) {
                throw new AccessDeniedException($this->trans("You do not have permission to edit the persons in the MCI."));
            }
            $person = new PersonEntity();
            $person->setUserId($uid);
            $person->setEmail($currentUserApi->get('email'));
            $person->setName($currentUserApi->get('uname'));
        } else {
            //to edit a person, you either need to be that user or be an admin
            $userID = $person->getUserId();
            if ( ($uid != $userID) && (!$this->hasPermission($this->name . '::', $person->getId() . '::', ACCESS_ADMIN))) {
                $this->addFlash('error',"You do not have permission to edit this person's information.");
                return $this->redirect($this->generateUrl('paustianpmcimodule_analysis_index'));
            }
            $doMerge = true;
        }

        //I need to add the use declaration for this class. 
        $form = $this->createForm( Person::class, $person);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            //Search to see if this Email and course have been registred
            $em = $this->getDoctrine()->getManager();
            $criteria = ['email' => $person->getEmail(), 'course' => $person->getCourse()];
            $personExists = $em->getRepository('Paustian\PMCIModule\Entity\PersonEntity')->findBy($criteria);
            if($personExists){
                $this->addFlash('status', $this->trans('This Email and course name has already been registered.'));
                return $this->redirect($this->generateUrl('paustianpmcimodule_person_edit'));
            }

            //Now notify the admin that someone wants the MCI
            $adminMail = $variableApi->getSystemVar('adminmail');
            $siteName = $variableApi->getSystemVar('sitename');
            $msgBody = $formData['name'] . $this->trans(' of ') . $formData['institution'] .  $this->trans(' has requested a copy of the MCI. Please email it to the address:\n' . $formData['email']);
            try {
                $message = (new Email())
                    ->from(new Address($formData['email'], $formData['name']))
                    ->to(new Address($adminMail, $siteName))
                    ->subject($this->trans('A reqeust for the MCI'))
                    ->html($msgBody);
                $mailer->send($message);

                if (!$doMerge) {
                    $em->persist($person);
                }
                $em->flush();
                $this->addFlash('status', $this->trans('Thank you for submitting your request. It will be authorized within 1 business day.'));
            } catch (TransportExceptionInterface $exception){
                $this->addFlash('status', $this->trans('Your messaged failed, please check your email address and name.'));
            }
            return $this->redirect($this->generateUrl('paustianpmcimodule_person_edit'));
        }

        return $this->render('@PaustianPMCIModule/Person/pmci_person_edit.html.twig', [
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
            return $response;
        }
        if (!$this->hasPermission($this->name . '::', $person->getId() . "::", ACCESS_DELETE)) {
            $this->addFlash('error',"You do not have permission to delete a person.");
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($person);
        $em->flush();
        $this->addFlash('status', $this->trans('Person Deleted.'));
        return $response;
    }



    /**
     * @Route("/modify")
     * @Theme("admin")
     * @param Request $request
     * @return Response
     */
    public function modifyAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $people = $em->getRepository("Paustian\PMCIModule\Entity\PersonEntity")->findAll();
        return $this->render('@PaustianPMCIModule/Person/pmci_person_modifyperson.html.twig', ['people' => $people]);
    }
}