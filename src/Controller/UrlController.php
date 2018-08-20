<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Url;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use App\Form\UrlForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Service\UrlChecker;

class UrlController extends AbstractController
{
    /**
     * @Route("/urls", name="urls")
     */
    public function index()
    {
    	$urls = $this->fetchUserUrls();

        return $this->generatePage('url/index.html.twig', ['urls' => $urls]);
    }

    public function fetchUserUrls()
    {
    	return $this->getDoctrine()->getRepository(User::class)->find($this->getUser()->getId())->getUrls();
    }

    public function generatePage(string $templeateName, array $data=[])
    {
        return $this->render($templeateName, $data);
    }

    /**
     * @Route("/urls/create", name="urls_create")
     */
    public function create(Request $request, UrlChecker $urlChecker)
	{
		// var_dump((bool) $urlChecker->checkUrl('https://symfony.com/')); die();

		$form = $this->generateForm();

    	$form->handleRequest($request);

    	if ($form->isSubmitted() && $form->isValid()) {
    		$this->saveUrl($url);

    		$this->createNotification('success', 'Url added!');

	    	return $this->redirectToRoute('urls');
    	}

        return $this->generatePage(
        	'url/create.html.twig', 
        	['form' => $form->createView()]
        );
	}

	public function generateForm()
	{
		$url = new Url();
    	return $this->createForm(UrlForm::class, $url);
	}

	public function saveUrl($url)
	{
		$url->setUser($this->getUser());
		$url->setIsShared(false);

    	$em = $this->getDoctrine()->getManager();
    	$em->persist($url);
    	$em->flush();
	}

	public function createNotification($name, $message)
	{
		return $this->addFlash($name, $message);
	}

    /**
     * @Route("/urls/share/{id}", name="urls_share")
     */
    public function share($id)
    {
	    $em = $this->getDoctrine()->getManager();
	    $url = $em->getRepository(Url::class)->find($id);

	    if (!$url) {
	        throw $this->createNotFoundException(
	            'Url does not exists'
	        );
	    }

	    $url->setIsShared(true);
	    $em->flush();

	    return $this->redirectToRoute('urls');
    }

    /**
     * @Route("/urls/shared", name="urls_shared")
     */
    public function shared_urls()
    {
    	$urls = $this->fetchSharedUrls();

        return $this->generatePage('url/shared.html.twig', ['urls' => $urls]);
    }

    public function fetchSharedUrls()
    {
    	return $this->getDoctrine()->getRepository(Url::class)->findBy(['is_shared' => true]);
    }
}