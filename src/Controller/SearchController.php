<?php

namespace App\Controller;

use App\Repository\VehicleRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class SearchController extends AbstractController
{
    public function __construct(private VehicleRepository $doctrine) {}

    #[Route('/search', name: 'search')]
    public function search(
        #[MapQueryParameter] string $plate = '',
        #[MapQueryParameter] ?DateTime $date_start = null,
        #[MapQueryParameter] ?DateTime $date_end = null,
    ): JsonResponse
    {
        // Set default values for date parameters if not provided
        if ($date_start === null) {
            $date_start = new \DateTimeImmutable('-2 hours');
        }
        if ($date_end === null) {
            $date_end = new \DateTimeImmutable('now');
        }

        // create a new Response object
        $response = new JsonResponse();

        $vehicleRepository = $this->doctrine;

        if (isset($plate) && !empty($plate)) {
            $matches = $vehicleRepository->findByPlate($plate, $date_start, $date_end);
            if (!$matches || empty($matches)) {
                $response->setStatusCode(Response::HTTP_NOT_FOUND);
                $response->setData(['message'=>'No results found.', 'results'=>[]]);
            } else {
                $response->setStatusCode(Response::HTTP_OK);
                $message = count($matches) === 1 ? 'result' : 'results';
                $response->setData(['message' => count($matches) . " $message found.", 'results' => $matches]);
            }
        } else {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setData(['message'=>'A vehicle license plate is required via the plate query string. e.g. `plate=AA%201234AB`.', 'results'=>[]]);
        }

        // set the response content type to application/json (not plain text for JSON)
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
