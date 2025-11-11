<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search')]
    public function search(): Response
    {
        // create a new Response object
        $response = new Response();

        // set the return value
        $response->setContent('Hello World!');

        // make sure we send a 200 OK status
        $response->setStatusCode(Response::HTTP_OK);

        // set the response content type to plain text
        $response->headers->set('Content-Type', 'text/plain');

        // send the response with appropriate headers
        $response->send();
    }
}
