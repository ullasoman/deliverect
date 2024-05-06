<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB,Log;

class DeliverectWebhookController extends Controller
{
    public function validateDeliveryJob(Request $request)
    {
        try {
            $request_data = $request->all();
        
            $response = [
                'jobId' => $request_data['jobId'],
                'canDeliver' => true,
                'distance' => 10,
                'pickupTimeETA' => $request_data['pickupTime'],
                'deliveryLocations' =>[
                    "deliveryId" => "ABC567",
                    "orderId" => $request_data['deliveryLocations'][0]['orderId'],
                    "deliveryTimeETA" => $request_data['deliveryLocations'][0]['deliveryTime']
                ],
                'price'=>[
                    "price" => $request_data['deliveryLocations'][0]['payment']['amount'],
                    "taxRate" => 0
                ]
            ];

            // return the response from validate job webhook
            return $response;

        } catch (\Exception $e) {
            // Handle any exceptions that occur during the request
            \Log::error('Failed to call the webhook: ' . $e->getMessage());
        }
        
    }
    public function createJob(Request $request){

        $data = $request->all();

        Log::info($data);

      //  return response()->json(['message' => 'Delivery job created successfully', 'data'=>$data], 200);
    }
    public function cancelJob(Request $request){

        $data = $request->all();

        \Log::info('cancel_job');

        \Log::info($data);

        \Log::info('cancel_job');

       // return response()->json(['message' => 'Delivery job cancelled successfully', 'data'=>$data], 200);
    }
}