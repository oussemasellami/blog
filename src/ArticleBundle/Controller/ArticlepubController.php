<?php

namespace ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use techeventBundle\Entity\Article;
use techeventBundle\Entity\Commentaire;
use Symfony\Component\HttpFoundation\Request;
use ArticleBundle\Form\ArticleType;
use Symfony\Component\Security\Core\User\UserInterface;
use techeventBundle\Repository\ArticleeRepository;
use techeventBundle\Repository\CommentaireRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ArticlepubController extends Controller
{
    public function addAction(Request $request)
    {

        $m = new Article();
        $form= $this->createForm(ArticleType::class, $m);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();

            $file = $m->getPhoto();
            $filename= md5(uniqid()) . '.' . $file->guessExtension();
            $file->move($this->getParameter('photos_directory'), $filename);
            $m->setPhoto($filename);
            $m->setHeurepubl(new \DateTime('now'));

            $em->persist($m);
            $em->flush();

            $this->addFlash('info', 'Created Successfully !');
        }
        return $this->render('@Article/front/add.html.twig', array(
            "Form"=> $form->createView()
        ));
    }
    public function listpostAction(Request $request)
    {

        $em=$this->getDoctrine()->getManager();
        $posts=$em->getRepository('techeventBundle:Article')->findAll();
        /**
         * @var $paginator \Knp\Component\Pager\Paginator
         */
        $paginator  = $this->get('knp_paginator');
        $result = $paginator->paginate(
            $posts, /* query NOT result */
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 6)
        );
        return $this->render('@Article/front/list.html.twig', array(
            "posts" =>$result
        ));

    }

    public function listpostadminAction(Request $request)
    {

        $em=$this->getDoctrine()->getManager();
        $posts=$em->getRepository('techeventBundle:Article')->findAll();
        /**
         * @var $paginator \Knp\Component\Pager\Paginator
         */
        $paginator  = $this->get('knp_paginator');
        $result = $paginator->paginate(
            $posts, /* query NOT result */
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 6)
        );
        return $this->render('@Article/front/listadmin.html.twig', array(
            "posts" =>$result
        ));

    }
    public function updatepostAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $p = $em->getRepository('techeventBundle:Article')->find($id);
        $form = $this->createForm(ArticleType::class, $p);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $file = $p->getPhoto();
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move($this->getParameter('photos_directory'), $filename);
            $p->setPhoto($filename);
            $p->setHeurepubl(new \DateTime('now'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($p);
            $em->flush();
            return $this->redirectToRoute('list_post');

        }
        return $this->render('@Article/front/update.html.twig', array(
            "form" => $form->createView()
        ));
    }

    public function deletepostAction(Request $request)
    {
        $id = $request->get('id');
        $em= $this->getDoctrine()->getManager();
        $Post=$em->getRepository('techeventBundle:Article')->find($id);
        $em->remove($Post);
        $em->flush();
        return $this->redirectToRoute('list_post');
    }

    public function showdetailedAction($id)
    {
        $em= $this->getDoctrine()->getManager();
        $p=$em->getRepository('techeventBundle:Article')->find($id);
        return $this->render('@Article/front/detailedpost.html.twig', array(
            'titre'=>$p->getTitre(),
            'date'=>$p->getheurepubl(),
            'photo'=>$p->getPhoto(),
            'descripion'=>$p->getDescription(),
            'posts'=>$p,
            'comments'=>$p,
            'id'=>$p->getId()
        ));
    }
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $requestString = $request->get('q');
        $posts =  $em->getRepository('techeventBundle:Article')->findEntitiesByString($requestString);
        if(!$posts) {
            $result['posts']['error'] = "Article non trouvé :( ";
        } else {
            $result['posts'] = $this->getRealEntities($posts);
        }
        return new Response(json_encode($result));
    }
    public function getRealEntities($posts){
        foreach ($posts as $posts){
            $realEntities[$posts->getId()] = [$posts->getPhoto(),$posts->getTitre()];

        }
        return $realEntities;
    }
    public function addCommentAction(Request $request, UserInterface $user)
    {


        $ref = $request->headers->get('referer');

        $post = $this->getDoctrine()->getManager()
            ->getRepository(Article::class)
            ->findPostByid($request->request->get('post_id'));

        $comment = new Commentaire();

        $comment->setUser($user);
        $comment->setPost($post);
        $comment->setcontenuecom($request->request->get('comment'));
        $comment->setheurecom(new \DateTime('now'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        $this->addFlash(
            'info', 'Comment published !.'
        );

        return $this->redirect($ref);

    }

    public function deleteCommentAction(Request $request)
    {
        $id = $request->get('id');
        $em= $this->getDoctrine()->getManager();
        $comment=$em->getRepository('techeventBundle:Commentaire')->find($id);
        $em->remove($comment);
        $em->flush();
        return $this->redirectToRoute('list_post');
    }
}
