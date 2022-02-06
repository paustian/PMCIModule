<?php

// ----------------------------------------------------------------------
// Original Author of file: Timothy Paustian
// Purpose of file:  Book administration display functions
// ----------------------------------------------------------------------

namespace Paustian\PMCIModule\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Paustian\PMCIModule\Entity\PersonEntity;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
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

    private $currUser;
    private $em;

    public function __construct(AbstractExtension $extension,
                                PermissionApiInterface $permissionApi,
                                VariableApiInterface $variableApi,
                                TranslatorInterface $translator,
                                CurrentUserApi $currentUserApi,
                                EntityManagerInterface $entityManagerInterface){
        parent::__construct($extension, $permissionApi, $variableApi, $translator);
        $this->currUser = $currentUserApi;
        $this->em = $entityManagerInterface;
    }
    /**
     * @Route("")
     * @param $request
     * @return Response The rendered output consisting mainly of the admin menu
     * 
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function index(Request $request) {
        return $this->edit($request);
    }

    /**
     *
     * @Route("/edit/{person}")
     * Edit or Delete a person using the MCI. This allows changes to a person
     * @param Request $request
     * @param MailerInterface $mailer
     * @param VariableApiInterface $variableApi
     * @param PersonEntity|null $person
     * @return Response The rendered output of the modifyconfig template.
     */
    public function edit(Request $request,
                               MailerInterface $mailer,
                               VariableApiInterface $variableApi,
                               PersonEntity $person = null) {
        $doMerge = false;
        //make sure the person is logged in. You need to be a user so I can keep track of you
        //and so the user of the MCI can have their data analyzed.
        if(!$this->currUser->isLoggedIn()) {
            $this->addFlash('error', $this->trans('You need to register as a user before you can obtain the MCI.'));
            return $this->redirect($this->generateUrl('zikulausersmodule_registration_register'));
        }
        $uid = $this->currUser->get('uid');

        if (null === $person) {
            if (!$this->hasPermission($this->name . '::', '::', ACCESS_COMMENT)) {
                throw new AccessDeniedException($this->trans("You do not have permission to edit the persons in the MCI."));
            }
            $person = new PersonEntity();
            $person->setUserId($uid);
            $person->setEmail($this->currUser->get('email'));
            $person->setName($this->currUser->get('uname'));
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
            $criteria = ['email' => $person->getEmail(), 'course' => $person->getCourse()];
            $personExists = $this->em->getRepository('Paustian\PMCIModule\Entity\PersonEntity')->findBy($criteria);
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
                    $this->em->persist($person);
                }
                $this->em->flush();
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
    public function delete(Request $request, PersonEntity $person) {
        $response = $this->redirect($this->generateUrl('paustianpmcimodule_person_edit'));
        if (null == $person) {
            return $response;
        }
        if (!$this->hasPermission($this->name . '::', $person->getId() . "::", ACCESS_DELETE)) {
            $this->addFlash('error',"You do not have permission to delete a person.");
            return $response;
        }
        $this->em->remove($person);
        $this->em->flush();
        $this->addFlash('status', $this->trans('Person Deleted.'));
        return $response;
    }



    /**
     * @Route("/modify")
     * @Theme("admin")
     * @param Request $request
     * @return Response
     */
    public function modify(Request $request) {
        $people = $this->em->getRepository("Paustian\PMCIModule\Entity\PersonEntity")->findAll();
        return $this->render('@PaustianPMCIModule/Person/pmci_person_modifyperson.html.twig', ['people' => $people]);
    }
}