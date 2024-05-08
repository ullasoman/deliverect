<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\PartnerOrder;
use App\Models\PartnerOrderPickupLocation;
use App\Models\PartnerOrderDeliveryLocation;
use DB,Log;

class DeliverectWebhookController extends Controller
{
    public function validateDeliveryJob(Request $request)
    {
        try {
            $request_data = $request->all();
        
            $response = [
                "jobId" => $request_data['jobId'],
                "canDeliver" => true,
                "distance" => 10,
                "pickupTimeETA" => $request_data['pickupTime'],
                "deliveryLocations" =>[
                    [
                        "deliveryId" => "ABC567",
                        "orderId" => $request_data['deliveryLocations'][0]['orderId'],
                        "deliveryTimeETA" => $request_data['deliveryLocations'][0]['deliveryTime']
                    ]
                ],
                "price"=>[
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

        try {
            // Create a new order object

            $request_data = $request->all();

            $order = [
                'partner_id' => 1,
                'order_id' => $request_data['deliveryLocations'][0]['orderId'],
                'job_id' => $request_data['jobId'],
                'account' => $request_data['account'],
                'pickup_time' => $request_data['pickupTime'],
                'transport_type' => $request_data['transportType'],
                'channel_order_display_id' => $request_data['deliveryLocations'][0]['channelOrderDisplayId'],
                'delivery_time' => $request_data['deliveryLocations'][0]['deliveryTime'],
                'package_size' => $request_data['deliveryLocations'][0]['packageSize'],
                'order_description' => $request_data['deliveryLocations'][0]['orderDescription'],
                'order_is_already_paid' => $request_data['deliveryLocations'][0]['payment']['orderIsAlreadyPaid'],
                'driver_tip' => $request_data['driverTip'],
                'amount' => $request_data['deliveryLocations'][0]['payment']['amount'],
                'payment_type' => $request_data['deliveryLocations'][0]['payment']['paymentType'],
            ];

            if($partnerOrder = PartnerOrder::create($order)){

                /* STORE PICKUP & DELIVERY LOCATION DETAILS*/

                $job_id = $request_data['jobId'];
                $order_id = $partnerOrder->id;
                $pickupDetailsArr = $request['pickupLocation'];
                $deliveryDetailsArr = $request['deliveryLocations'][0];

                $this->storePickupDetails($job_id,$order_id,$pickupDetailsArr);
                $this->storeDeliveryDetails($job_id,$order_id,$deliveryDetailsArr);

                /* PUSHING EXTERNAL ORDER TO SLIDER SERVER */
                $this->puhingOrderToSliderServer($request_data);
                /* PUSHING EXTERNAL ORDER TO SLIDER SERVER */
            }

            $response = [
                "jobId" => $request_data['jobId'],
                "canDeliver" => true,
                "pickupTimeETA" => $request_data['pickupTime'],
                "distance" => 10,
                "externalJobId" => "DJ123456",
                "price"=>[
                    "price" => $request_data['deliveryLocations'][0]['payment']['amount'],
                    "taxRate" => 0
                ],
                "courier"=>[
                    "courierId" => "D1234",
                    "firstName" => "Delivery",
                    "lastName" => "Rider",
                    "phoneNumber" => "0032494112233",
                    "transportType" => "bicycle"
                ],
                "deliveryLocations" =>[
                    [
                        "deliveryId" => "ABC567",
                        "orderId" => $request_data['deliveryLocations'][0]['orderId'],
                        "channelOrderDisplayId" => $request_data['deliveryLocations'][0]['channelOrderDisplayId'],
                        "deliveryTimeETA" => $request_data['deliveryLocations'][0]['deliveryTime'],
                        "deliveryRemarks" => ""
                    ]
                ],
            ];

            // return the response from validate job webhook
            return $response;

        } catch (\Exception $e) {
            \Log::error('Failed to call the create_job webhook: ' . $e->getMessage());
        }

        $request_data = $request->all();

        

      //  return response()->json(['message' => 'Delivery job created successfully', 'data'=>$data], 200);
    }
    public function storePickupDetails($job_id,$order_id,$request_data){

        $data = [
            'partner_id' => 1,
            'job_id' => $job_id,
            'order_id' => $order_id,
            'location' => $request_data['location'],
            'name' => $request_data['name'],
            'remarks' => $request_data['remarks'],
            'street' => $request_data['street'],
            'street_number' => $request_data['streetNumber'],
            'postal_code' => $request_data['postalCode'],
            'city' => $request_data['city'],
            'latitude' => $request_data['latitude'],
            'longitude' => $request_data['longitude'],
        ];

        PartnerOrderPickupLocation::create($data);

    }
    public function storeDeliveryDetails($job_id,$order_id,$request_data){
        
        $data = [
            'partner_id' => 1,
            'job_id' => $job_id,
            'order_id' => $order_id,
            'company' => $request_data['company'],
            'name' => $request_data['name'],
            'street' => $request_data['street'],
            'street_number' => null,
            'postal_code' => $request_data['postalCode'],
            'city' => $request_data['city'],
            'delivery_remarks' => $request_data['deliveryRemarks'],
            'phone' => $request_data['phone'],
            'latitude' => $request_data['latitude'],
            'longitude' => $request_data['longitude'],
        ];

        PartnerOrderDeliveryLocation::create($data);

    }
    public function cancelJob(Request $request){

        $data = $request->all();

        \Log::info('cancel_job');

        \Log::info($data);

        \Log::info('cancel_job');

       // return response()->json(['message' => 'Delivery job cancelled successfully', 'data'=>$data], 200);
    }
    public function puhingOrderToSliderServer($request_data){

        try {

            $pickup_lat = $request_data['pickupLocation']['latitude'];
            $pickup_lng = $request_data['pickupLocation']['longitude'];

            $delivery_lat = $request_data['deliveryLocations'][0]['latitude'];
            $delivery_lng = $request_data['deliveryLocations'][0]['longitude'];

            $latitude = [$pickup_lat,$delivery_lat];
            $longitude = [$pickup_lng,$delivery_lng];
            
            $getdata = GoogleDistanceMatrix($latitude, $longitude);

            $order_data = [
                "isExternalOrder" => true,
                "service_type" => 1,
                "vehicle_type" => 1,
                "schedule_time" => null,
                "tip" => $request_data['driverTip'],
                "receiver_phone_number" => $request_data['deliveryLocations'][0]['phone'],
                "coupon_code" => null,
                "distance" => $getdata['distance'],
                "duration" => $getdata['duration'],
                "tasks" =>[
                    [
                        "task_type_id" => 1,
                        "address" => $request_data['pickupLocation']['name'],
                        "latitude" => $request_data['pickupLocation']['latitude'],
                        "longitude" => $request_data['pickupLocation']['longitude'],
                        "short_name" => $request_data['pickupLocation']['street'],
                        "flat_no" => $request_data['pickupLocation']['streetNumber'],
                        "direction" => $request_data['pickupLocation']['remarks'],
                        "city" => $request_data['pickupLocation']['city']
                    ],
                    [
                        "task_type_id" => 2,
                        "address" => $request_data['deliveryLocations'][0]['name'],
                        "latitude" => $request_data['deliveryLocations'][0]['latitude'],
                        "longitude" => $request_data['deliveryLocations'][0]['longitude'],
                        "short_name" => $request_data['deliveryLocations'][0]['street'],
                        "flat_no" => null,
                        "direction" => $request_data['deliveryLocations'][0]['deliveryRemarks'],
                        "city" => $request_data['deliveryLocations'][0]['city']
                    ]
                ],
            ];

            $client = new Client();

            $base_url = 'http://127.0.0.1:8000';

            $slider_webhook = $base_url.'/api/v1/create-external-order';

            $response = $client->request('POST', $slider_webhook, [
                'headers' => [
                    'accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $order_data, // Send the payload data here
            ]);

            // Convert response body to string
            $responseBody = $response->getBody();

            return $responseBody;
        } catch (\Exception $e) {
            \Log::error('Failed to push the order to slider: ' . $e->getMessage());
        }
    }
}