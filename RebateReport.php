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
    private $ksCards;
    private $swCards;
    
    private $ksUnsoldCards;
    private $ksNumUnsoldCards = 0;
    private $ksSoldTotal = 0;
    private $ksUnsoldTotal = 0;
    
    private $swUnsoldCards;
    private $swNumUnsoldCards = 0;
    private $swSoldTotal = 0;
    private $swUnsoldTotal = 0;
 
    function __construct($students, $rebatePercentages, $ksCards, $swCards)
    {
        $this->students = $students;
        $this->ksRebatePercent = $rebatePercentages->getKsRebatePercentage();
        $this->swRebatePercent = $rebatePercentages->getSwRebatePercentage();
        $this->boostersPercent = $rebatePercentages->getBoostersPercentage();
        $this->ksCards = $ksCards;
        $this->swCards = $swCards;
        
        $this->calculateStoreTotals();
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
                
        .tg .tg-undr {
            border-bottom: 1px solid black;
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
            font-size: 18px;
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
        $this->table .= "<table class='tg'>";
    }
    
    private function endTable()
    {
        $this->table .= '</table>';
    }
     
    private function writeTitle($title)
    {
        $this->table .= "<tr><th class='tg-amwm' colspan='7'>$title</th></tr>";
    }
    
    private function writeLine()
    {
        $this->table .= "<tr><td colspan='7'></td></tr>";
    }
    
    private function writeUnderline()
    {
        $this->table .= "<tr><td class='tg-undr' colspan='7'></td></tr>";
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
    
    private function writeHeader()
    {
        $this->table .=         
        "<tr>" .
            "<td class='tg-9hbo'></td>" .
            "<td class='tg-9hbo'></td>" .
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
    
    private function writeStoreCardsTotal($store, $cardsTotal, $rebatePercentage, $boostersPercentage)
    {
        $rebate = $cardsTotal * $rebatePercentage;
        $boostersShare = $rebate * $boostersPercentage;
        $studentShare = $rebate - $boostersShare;
        
        $cardsTotalAmt = $this->numberToMoney($cardsTotal);
        $rebateAmt = $this->numberToMoney($rebate);
        $boostersShareAmt = $this->numberToMoney($boostersShare);
        $studentShareAmt = $this->numberToMoney($rebate - $boostersShare);
        
        $this->writeStoreCardsTotalValues($store . " cards total", $cardsTotalAmt, 
                $rebateAmt, $boostersShareAmt, $studentShareAmt);
    }
    
    private function writeStoreCardsTotalValues($store, $total, $rebate, $boostersShare, $studentShare) {
        
        $this->table .=
        "<tr>" .
            "<td class='tg-l2oz' colspan='2'>$store</td>" .
            "<td class='tg-lqy6'>$total</td>" .
            "<td class='tg-lqy6'>$rebate</td>" .
            "<td class='tg-lqy6'>$boostersShare</td>" .
            "<td class='tg-lqy6'>$studentShare</td>" .
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
                
        $this->writeLine();
    }
    
    private function writeNonStudentStoreCardsTotal($name, $ksTotal, $swTotal)
    {
        $allStoreTotal = $ksTotal + $swTotal;
        $rebate = ($ksTotal * $this->ksRebatePercent) + ($swTotal * $this->swRebatePercent);
        $boostersShare = $rebate;
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
            "<td class='tg-lqy6'>$boostersShareAmt</td>" .
            "<td class='tg-lqy6'>$studentShareAmt</td>" .
        "</tr>";
                
        $this->writeLine();
    }
    
    private function writeStudentCards()
    {
        $this->writeTitle("Grocery Card Reloads per Student");
        
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
                $this->writeStoreCardsTotal("King Soopers", $ksCardsTotal, $this->ksRebatePercent, $this->boostersPercent);
            }
            
            foreach($student["swCards"] as $cardData)
            {
                $this->writeCardReload($cardData["cardNumber"], $cardData["cardHolder"], $cardData["total"]);
            }
            if ($swCardCount > 0)
            {
                $this->writeStoreCardsTotal("Safeway", $swCardsTotal, $this->swRebatePercent, $this->boostersPercent);
            }
            
            if ($ksCardCount > 0 || $swCardCount > 0)
            {
                $this->writeAllStoreCardsTotal($name, $ksCardsTotal, $swCardsTotal);
            }
        }
        $this->writeUnderline();
        $this->writeLine();
    }
    
    private function calculateStoreTotals() 
    {       
        foreach($this->ksCards as $cardData) {
            if ($cardData["sold"] == "f") {
                $this->ksUnsoldCards[$this->ksNumUnsoldCards++] = $cardData;
                $this->ksUnsoldTotal += $cardData["total"];
            }
            else {
                $this->ksSoldTotal += $cardData["total"];
            }
        }
        
        foreach($this->swCards as $cardData) {
            if ($cardData["sold"] == "f") {
                $this->swUnsoldCards[$this->swNumUnsoldCards++] = $cardData;
                $this->swUnsoldTotal += $cardData["total"];
            }
            else {
                $this->swSoldTotal += $cardData["total"];
            }
        }
    }
    
    private function writeNonStudentCards()
    {
        if ($this->ksNumUnsoldCards > 0 || $this->swNumUnsoldCards > 0) {
            
            $this->writeTitle("Grocery Cards Unassociated with a Student");           
            $this->writeCardHeaders();
            
            foreach($this->ksUnsoldCards as $cardData) {
                $card = $cardData["cardNumber"];
                $cardHolder = $cardData["cardHolder"];
                $amount = $cardData["total"];
                $this->writeCardReload($card, $cardHolder, $amount);
            }
            if ($this->ksNumUnsoldCards > 0) {
                $this->writeStoreCardsTotal("King Soopers", $this->ksUnsoldTotal, $this->ksRebatePercent, 1.00);
            }
            
            foreach($this->swUnsoldCards as $cardData) {
                $card = $cardData["cardNumber"];
                $cardHolder = $cardData["cardHolder"];
                $amount = $cardData["total"];
                $this->writeCardReload($card, $cardHolder, $amount);
            }
            if ($this->swNumUnsoldCards > 0) {
                $this->writeStoreCardsTotal("Safeway", $this->swUnsoldTotal, $this->swRebatePercent, 1.00);
            }
            
            $this->writeNonStudentStoreCardsTotal($name, $this->ksUnsoldTotal, $this->swUnsoldTotal);
            
            $this->writeUnderline();
            $this->writeLine();
        }
    }
    
    private function writeOverallGroceryTotals() {
        $this->writeTitle("Grocery Card Totals");
        $this->writeHeader();
        
        $ksTotal = $this->ksSoldTotal + $this->ksUnsoldTotal;
        $ksRebate = $ksTotal * $this->ksRebatePercent;
        $ksBoostersShare = ($this->ksUnsoldTotal * $this->ksRebatePercent) + 
                           ($this->ksSoldTotal * $this->ksRebatePercent * $this->boostersPercent);
        $ksStudentShare = $ksRebate - $ksBoostersShare;
        
        $ksTotalAmt = $this->numberToMoney($ksTotal);
        $ksRebateAmt = $this->numberToMoney($ksRebate);
        $ksBoostersShareAmt = $this->numberToMoney($ksBoostersShare);
        $ksStudentShareAmt = $this->numberToMoney($ksStudentShare);
        
        $this->writeStoreCardsTotalValues("King Soopers", $ksTotalAmt, $ksRebateAmt,
                $ksBoostersShareAmt, $ksStudentShareAmt);
        
        $swTotal = $this->swSoldTotal + $this->swUnsoldTotal;
        $swRebate = $swTotal * $this->swRebatePercent;
        $swBoostersShare = ($this->swUnsoldTotal * $this->swRebatePercent) + 
                           ($this->swSoldTotal * $this->swRebatePercent * $this->boostersPercent);
        $swStudentShare = $swRebate - $swBoostersShare;
        
        $swTotalAmt = $this->numberToMoney($swTotal);
        $swRebateAmt = $this->numberToMoney($swRebate);
        $swBoostersShareAmt = $this->numberToMoney($swBoostersShare);
        $swStudentShareAmt = $this->numberToMoney($swStudentShare);
        
        $this->writeStoreCardsTotalValues("Safeway", $swTotalAmt, $swRebateAmt,
                $swBoostersShareAmt, $swStudentShareAmt);
        
        $total = $ksTotal + $swTotal;
        $rebate = $ksRebate + $swRebate;
        $boostersShare = $ksBoostersShare + $swBoostersShare;
        $studentShare = $ksStudentShare + $swStudentShare;
        
        $totalAmt = $this->numberToMoney($total);
        $rebateAmt = $this->numberToMoney($rebate);
        $boostersShareAmt = $this->numberToMoney($boostersShare);
        $studentShareAmt = $this->numberToMoney($studentShare);
        
        $this->writeStoreCardsTotalValues("Total", $totalAmt, $rebateAmt,
                $boostersShareAmt, $studentShareAmt);
    }
    
    private function buildTable()
    {
        $this->startTable();
        $this->writeStudentCards();
        $this->writeNonStudentCards();
        $this->writeOverallGroceryTotals();
        $this->endTable();
    }
    
    public function getTable()
    {
        return $this->table;
    }
}
