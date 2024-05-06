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
            $data = $request->all();

            \Log::info('validate job called');

            // Call the same webhook with the request data using Guzzle
            // $client = new Client();

            // $response = $client->request('POST', 'https://slider-app.com/api/v1/validate_job', [
            //     'json' => $data,
            //     'headers' => [
            //         'accept' => 'application/json',
            //     ]
            // ]);
            return response()->json(['message' => 'Delivery job created successfully', 'data'=>$data], 200);

            // \Log::info($response->getBody()->getContents());
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