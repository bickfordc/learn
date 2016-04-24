<?php

/**
 * Description of RebateReport
 *
 * @author BickfordC
 */
class RebateReport {
    
    private $students;
    private $style;
    private $table;
    private $ksRebatePercent;
    private $swRebatePercent;
    private $boostersPercent;
 
    function __construct($students, $rebatePercentages)
    {
        $this->ksRebatePercent = $rebatePercentages->getKsRebatePercentage();
        $this->swRebatePercent = $rebatePercentages->getSwRebatePercentage();
        $this->boostersPercent = $rebatePercentages->getBoostersPercentage();
        $this->students = $students;
        ksort($this->students);
        $this->initStyle();
        $this->buildTable();
    }
    
    private function initStyle()
    {
        $this->style =<<<EOF
        <style type="text/css">
        .tg {
            border-collapse: collapse;
            border-spacing: 0;
            border: none;
            margin: 0px auto;
        }

        .tg td {
            font-family: verdana, Arial, sans-serif;
            font-size: 14px;
            padding: 10px 5px;
            border-style: solid;
            border-width: 0px;
            overflow: hidden;
            word-break: normal;
        }

        .tg th {
            font-family: verdana, Arial, sans-serif;
            font-size: 14px;
            font-weight: normal;
            padding: 10px 5px;
            border-style: solid;
            border-width: 0px;
            overflow: hidden;
            word-break: normal;
        }

        .tg .tg-erlg {
            font-weight: bold;
            background-color: #efefef;
            vertical-align: top;
        }

        .tg .tg-b3sr {
            font-weight: bold;
            /*background-color: #efefef;*/
            vertical-align: top;
            border-top: 1px solid black;
            border-right: 1px solid black;
            border-bottom: 1px solid black;
        }        
        
        .tg .tg-r3sr {
            font-weight: bold;
            color: red;
            vertical-align: top;
            border-top: 1px solid red;
            border-right: 1px solid red;
            border-bottom: 1px solid red;
        }   
                
        .tg .tg-lqy6 {
            text-align: right;
            vertical-align: top;
        }
                
        .tg .tg-b3sl {
            text-align: right;
            vertical-align: top;
            border-top: 1px solid black;
            border-left: 1px solid black;
            border-bottom: 1px solid black;
        }

        .tg .tg-r3sl {
            color: red;
            text-align: right;
            vertical-align: top;
            border-top: 1px solid red;
            border-left: 1px solid red;
            border-bottom: 1px solid red;
        }
                
        .tg .tg-amwm {
            font-weight: bold;
            text-align: center;
            vertical-align: top;
        }

        .tg .tg-9hbo {
            font-weight: bold;
            vertical-align: top;
        }

        .tg .tg-yw4l {
            vertical-align: top;
        }

        .tg .tg-fl7z {
            color: #fe0000;
            text-align: right;
            vertical-align: top;
        }

        .tg .tg-l2oz {
            font-weight: bold;
            text-align: left;
            vertical-align: top;
            padding-left: 80px;
        }
    </style>              
EOF;
        
    }
    
    private function startTable()
    {
        $this->table .= $this->style;
        $this->table .= '<table class="tg"><tr><th class="tg-amwm" colspan="7">' .
                        'Grocery card reloads per student</th></tr>';
    }
    
    private function endTable()
    {
        $this->table .= '</table>';
    }
    
    private function writeStudentHeader($name)
    {
        $this->table .= "<tr><td class='tg-erlg' colspan='7'>$name</td></tr>";
    }
    
    private function writeCardHeaders()
    {
        $this->table .=         
        "<tr>" .
            "<td class='tg-9hbo'>Card Number</td>" .
            "<td class='tg-9hbo'>Card Holder</td>" .
            "<td class='tg-9hbo'>Amount</td>" .
            "<td class='tg-9hbo'>Rebate</td>" .
            "<td class='tg-9hbo'>Boosters Share</td>" .
            "<td class='tg-9hbo'>Student Share</td>" .
            "<td class='tg-yw4l'></td>" .
        "</tr>";
    }

    private function writeCardReload($card, $cardHolder, $amount)
    {
        $amount = $this->numberToMoney($amount);
        $this->table .=
        "<tr>" .
            "<td class='tg-yw4l'>$card</td>" .
            "<td class='tg-yw4l'>$cardHolder</td>" .
            "<td class='tg-lqy6'>$amount</td>" .
            "<td class='tg-yw4l'></td>" .
            "<td class='tg-yw4l'></td>" .
            "<td class='tg-yw4l'></td>" .
            "<td class='tg-yw4l'></td>" .
        "</tr>";
    }
    
    private function numberToMoney($number)
    {
        // Not using PHP's money_format since not available on Windows.
        $money = "";
        if ($number < 0)
        {
            $money = "-$" . sprintf("%01.2f", abs($number));
        }
        else
        {
            $money = "$" . sprintf("%01.2f", $number);
        }
        return $money;
    }
    
    private function writeStoreCardsTotal($store, $cardsTotal, $rebatePercentage)
    {
        $rebate = $cardsTotal * $rebatePercentage;
        $boostersShare = $rebate * $this->boostersPercent;
        $studentShare = $rebate - $boostersShare;
        
        $cardsTotalAmt = $this->numberToMoney($cardsTotal);
        $rebateAmt = $this->numberToMoney($rebate);
        $boostersShareAmt = $this->numberToMoney($boostersShare);
        $studentShareAmt = $this->numberToMoney($rebate - $boostersShare);
        
        $this->table .=
        "<tr>" .
            "<td class='tg-l2oz' colspan='2'>$store cards total</td>" .
            "<td class='tg-lqy6'>$cardsTotalAmt</td>" .
            "<td class='tg-lqy6'>$rebateAmt</td>" .
            "<td class='tg-lqy6'>$boostersShareAmt</td>" .
            "<td class='tg-lqy6'>$studentShareAmt</td>" .
            "<td class='tg-yw4l'></td>" .
        "</tr>";
    }
    
    private function writeAllStoreCardsTotal($name, $ksTotal, $swTotal)
    {
        $allStoreTotal = $ksTotal + $swTotal;
        $rebate = ($ksTotal * $this->ksRebatePercent) + ($swTotal * $this->swRebatePercent);
        $boostersShare = $rebate * $this->boostersPercent;
        $studentShare = $rebate - $boostersShare;
                
        $allStoreTotalAmt = $this->numberToMoney($allStoreTotal);
        $rebateAmt = $this->numberToMoney($rebate);
        $boostersShareAmt = $this->numberToMoney($boostersShare);
        $studentShareAmt = $this->numberToMoney($studentShare);
        
        $this->table .=
        "<tr>" .
            "<td class='tg-l2oz' colspan='2'>Grocery cards total</td>" .
            "<td class='tg-lqy6'>$allStoreTotalAmt</td>" .
            "<td class='tg-lqy6'>$rebateAmt</td>" .
            "<td class='tg-lqy6'>$boostersShareAmt</td>";
        
        if ($studentShare < 0) {
            $this->table .=               
            "<td class='tg-r3sl'>$studentShareAmt</td>" .
            "<td class='tg-r3sr'>$name</td>";
        }
        else {
            $this->table .= 
            "<td class='tg-b3sl'>$studentShareAmt</td>" .
            "<td class='tg-b3sr'>$name</td>" ;            
        }
                
        $this->table .= "<tr><td colspan='7'></td></tr></tr>";
    }
    
    private function writeStudents()
    {
        foreach($this->students as $student)
        {
            $name = $student["first"] . " " . $student["last"];
            $ksCardCount = count($student["ksCards"]);
            $swCardCount = count($student["swCards"]);
            $ksCardsTotal = $student["ksCardsTotal"];
            $swCardsTotal = $student["swCardsTotal"];
            
            $this->writeStudentHeader($name);
            $this->writeCardHeaders();
            
            foreach($student["ksCards"] as $cardData)
            {
                $this->writeCardReload($cardData["cardNumber"], $cardData["cardHolder"], $cardData["total"]);
            }
            if ($ksCardCount > 0)
            {
                $this->writeStoreCardsTotal("King Soopers", $ksCardsTotal, $this->ksRebatePercent);
            }
            
            foreach($student["swCards"] as $cardData)
            {
                $this->writeCardReload($cardData["cardNumber"], $cardData["cardHolder"], $cardData["total"]);
            }
            if ($swCardCount > 0)
            {
                $this->writeStoreCardsTotal("Safeway", $swCardsTotal, $this->swRebatePercent);
            }
            
            if ($ksCardCount > 0 || $swCardCount > 0)
            {
                $this->writeAllStoreCardsTotal($name, $ksCardsTotal, $swCardsTotal);
            }
        }
    }
    
    private function buildTable()
    {
        $this->startTable();
        $this->writeStudents();
        $this->endTable();
    }
    
    public function getTable()
    {
        return $this->table;
    }
}
