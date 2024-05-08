<?php

use Carbon\Carbon;
/* ----------- Function to Get distance and duration from coordinates ----------- */
if (!function_exists('GoogleDistanceMatrix')) {
    function GoogleDistanceMatrix($latitude, $longitude)
    {
        $lengths = count($latitude) - 1;
        $value = [];

        $MAP_KEY = 'AIzaSyCpbRyXKoEgm5mE5qCmJ1ciM_8fQeyPe5c';
        $distance_unit = 'metric';

        for ($i = 1; $i<=$lengths; $i++) {
            $count  = 0;
            $count1 = 1;
            $ch = curl_init();
            $headers = array('Accept: application/json',
                    'Content-Type: application/json',
                    );
            $url =  'https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins='.$latitude[$count].','.$longitude[$count].'&destinations='.$latitude[$count1].','.$longitude[$count1].'&key='.$MAP_KEY.'';
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            $result = json_decode($response);
            curl_close($ch); // Close the connection
            $new =   $result;
            array_push($value, $result->rows[0]->elements);
            $count++;
            $count1++;
       
        }

        if (isset($value)) {
            $totalDistance = 0;
            $totalDuration = 0;
            foreach ($value as $item) {
                
                $totalDistance = $totalDistance + (isset($item[0]->distance) ? $item[0]->distance->value : 0);
                $totalDuration = $totalDuration + (isset($item[0]->duration) ? $item[0]->duration->value : 0);
            }


            if ($distance_unit == 'metric') {
                $data['distance'] = round($totalDistance/1000, 2);      //km
                
            } else {
                $data['distance'] = round($totalDistance/1609.34, 2);  //mile
            }
            //
            $newvalue = round($totalDuration/60, 2);
            $whole = floor($newvalue);
           
            $fraction = $newvalue - $whole;

            if ($fraction >= 0.60) {
                $data['duration'] = $whole + 1;
            } else {
                $data['duration'] = $whole;
            }
        }

        return $data;
    }
}