<?php

namespace App\Controller;

use App\Repository\VehicleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    public function __construct(private VehicleRepository $doctrine)
    {
    }

    private function returnInvalidDateResponse()
    {
        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        $response->setData(
            [
                'message' => 'The datetime query parameter must be a valid date in the format YYYY-MM-DD HH:MM:SS.',
                'results' => [],
            ],
        );
        // set the response content type to application/json (not plain text for JSON)
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    #[Route('/search', name: 'search')]
    public function search(
        #[MapQueryParameter] string $plate = '',
        #[MapQueryParameter] string $datetime = '',
        #[MapQueryParameter] int $window = 120,
    ): JsonResponse {
        // Set default values for date parameters if not provided
        if (empty($datetime)) {
            $calculateExpiredParkingFrom = new \DateTimeImmutable('now');
        } else {
            // Test for a valid datetime format
            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datetime)) {
                return $this->returnInvalidDateResponse();
            } else {
                try {
                    $calculateExpiredParkingFrom = new \DateTimeImmutable($datetime);
                } catch (\Exception $e) {
                    return $this->returnInvalidDateResponse();
                }
            }
        }

        // Calculate parking duration window
        $parkingWindow = new \DateInterval('PT'.$window.'M');

        $latestSafeParkedTime = $calculateExpiredParkingFrom->sub($parkingWindow);

        // create a new Response object
        $response = new JsonResponse();

        $vehicleRepository = $this->doctrine;

        if (isset($plate) && !empty($plate)) {
            $matches = $vehicleRepository->findByPlate($plate);
            if (!$matches || empty($matches)) {
                $response->setStatusCode(Response::HTTP_NOT_FOUND);
                $response->setData([
                    'message' => 'No results found.',
                    'results' => [
                        [
                            'license_plate' => $plate,
                            'time_in' => null,
                            'expired' => true,
                            'expiration_time' => null,
                        ],
                    ],
                ]);
            } else {
                $response->setStatusCode(Response::HTTP_OK);
                $message = count($matches).' ';
                $message .= 1 === count($matches) ? 'result' : 'results';
                $message .= ' found.';
                for ($i = 0; $i < count($matches); ++$i) {
                    $time_in = new \DateTimeImmutable($matches[$i]['time_in']);
                    $time_in_str = $time_in->format('Y-m-d H:i:s');
                    $expired_at = $time_in->add($parkingWindow); // Adds parking window to time_in

                    // Parking is expired if the expiration time is before the search window end
                    $is_expired = $expired_at < $latestSafeParkedTime;

                    $matches[$i] = [
                        'license_plate' => $matches[$i]['license_plate'],
                        'time_in' => $time_in_str,
                        'expired' => $is_expired,
                        'expiration_time' => $expired_at->format('Y-m-d H:i:s'),
                    ];
                }
                $response->setData(['message' => $message, 'results' => $matches]);
            }
        } else {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setData(
                [
                    'message' => 'A vehicle license plate is required via the plate query string. e.g. `plate=AA%201234AB`.',
                    'results' => [],
                ],
            );
        }

        // set the response content type to application/json (not plain text for JSON)
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
