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
        
        \Log::info('storeDeliveryDetails');
        \Log::info($request_data);
        \Log::info('storeDeliveryDetails');

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
}