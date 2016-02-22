<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Achat;
use AppBundle\Entity\Concert;
use AppBundle\Type\AchatType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;


class AchatController extends Controller
{
    /**
     * @Route("/achat/{id}", name="achat")
     * @Template("AppBundle:Achat:buy.html.twig")
     */
    public function buyAction(Request $request, $id)
    {
        $concert = $this->getDoctrine()->getRepository('AppBundle:Concert')->find($id);//recuperer le concert de dont id

        $achat = new Achat();
        if (null != $concert) {
        $achat->setConcert($concert);}

        $form = $this->createForm(AchatType::class, $achat);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($achat);
            $nbrPlace =$form['nbrPlace']->getData();
            $mail=$form['mail']->getData();

            $concert->setNbrPlace($concert->getNbrPlace()-$nbrPlace);
            $em->flush();

            $this->addFlash('success', 'The buying has been successfully added.');

            // pour envoyer un mail de confirmation


            $mailer = $this->get('mailer');
            $pseudo = $form['nom']->getData();
            $name_concert= $concert->getNom();
            $date =$concert->getDate();
            $message = \Swift_Message::newInstance()
                ->setSubject('Confirmation')
                ->setFrom('samsoum.infor@gmail.com')
                ->setTo($mail)
                ->setBody($this->renderView('AppBundle:Email:email.txt.twig', array('nom' =>$name_concert,'date'=>$date, 'nbrPlace'=>$nbrPlace,'pseudo'=>$pseudo )))
            ;
            $mailer->send($message);

              return $this->render('AppBundle:Email:email.html.twig', array('nom' =>$name_concert,'date'=>$date, 'nbrPlace'=>$nbrPlace,'pseudo'=>$pseudo ));


        }
        return array('achatForm' => $form->createView());
    }
}
