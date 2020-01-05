<?php

namespace App\Service;

class ActivityService
{

//Calculates how much the given activity costs
    function calculateCostPerActivity($act){

        $hourlyRate = $act->getCustomer()->getRate()->getHourlyRate();
        $overtime = 0;
        //get amount of hours worked
        $hours = $act->getStartTime()->diff($act->getEndTime())->h;
        if( $hours >= 8){
            $overtime = $hours - 8;
            $hours = 8;
        }

        $cost = ($hours * $hourlyRate) + ($overtime * $hourlyRate * 1.2);

        //increase the cost if the work was done on a saturday or sunday
        if ($act->getStartTime()->format("N") == 6){
            $cost *= 1.5;
        }else if ($act->getStartTime()->format("N") == 7){
            $cost *= 2;
        }

        return($cost);
    }

//Calculates how much the transport costs were for an activity
    function calculateTransportCostsPerActivity($act){
        $transportCostRate = $act->getCustomer()->getRate()->getTransportCostRate();
        return($act->getTransportDistance() * $transportCostRate);
    }

    function weekdayFromInt($startTime){
        switch($startTime){
            case 1:
                return "Maandag";
                break;
            case 2:
                return "Dinsdag";
                break;
            case 3:
                return "Woensdag";
                break;
            case 4:
                return "Donderdag";
                break;
            case 5:
                return "Vrijdag";
                break;
            case 6:
                return "Zaterdag";
                break;
            case 7:
                return "Zondag";
                break;

        }
    }
}