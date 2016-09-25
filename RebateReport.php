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
    private $tableForHtml = null;
    private $ksRebatePercent;
    private $swRebatePercent;
    private $boostersPercent;
    private $ksCards;
    private $swCards;
    private $scripFamilies;
    private $nonStudentScripFamilies;
    private $srcFileNames;
    
    private $ksUnsoldCards;
    private $ksNumUnsoldCards = 0;
    private $ksSoldTotal = 0;
    private $ksUnsoldTotal = 0;
    
    private $swUnsoldCards;
    private $swNumUnsoldCards = 0;
    private $swSoldTotal = 0;
    private $swUnsoldTotal = 0;

    private $studentScripTotalValue = 0 ;
    private $studentScripTotalRebate = 0;
    private $nonStudentScripTotalValue = 0;
    private $nonStudentScripTotalRebate = 0;
       
    function __construct($students, $rebatePercentages, $ksCards, $swCards, 
            $srcFileNames, $scripFamilies)
    {
        $this->students = $students;
        $this->ksRebatePercent = $rebatePercentages->getKsRebatePercentage();
        $this->swRebatePercent = $rebatePercentages->getSwRebatePercentage();
        $this->boostersPercent = $rebatePercentages->getBoostersPercentage();
        $this->ksCards = $ksCards;
        $this->swCards = $swCards;
        $this->srcFileNames = $srcFileNames;
        $this->scripFamilies = $scripFamilies;
        
        $this->calculateTotals();
        ksort($this->students);
        $this->initStyle();
    }
    
    private function initStyle()
    {       
        $this->style =<<<EOF
        <style type="text/css">
            
    * {
        font-family: arial;
    }
    
    table { 
        border-collapse: collapse;
        border: none;
        margin: auto;
    }

    td {
        font-size: 14px;
        padding: 10px 5px;
/*        border-style: solid;
        border-width: 0px; */
        overflow: hidden;
    }

    th {
        font-size: 14px;
        font-weight: normal;
        padding: 10px 5px;
 /*       border-style: solid;
        border-width: 0px; */
        overflow: hidden;
    }

    .tg-title {   
        font-size: 18px;
        font-weight: bold;
        color: green;
        text-align: center;
    }

    .tg-sthd {
        font-weight: bold;
        background-color: #efefef;
    }

    .tg-undr {
        border-bottom: 1px solid black
    }

    .tg-b3sr {
        font-weight: bold; 
        /*border-top: 1px solid black;
        border-right: 1px solid black;
        border-bottom: 1px solid black;*/
    }

    .tg-r3sr {
        font-weight: bold;
        color: red;
        /*border-top: 1px solid red;
        border-right: 1px solid red;
        border-bottom: 1px solid red;*/
    }

    .tg-b3sl {
        text-align: right;
        /*border-top: 1px solid black;
        border-left: 1px solid black;
        border-bottom: 1px solid black;*/
    }

    .tg-r3sl {
        text-align: right;
        color: red;
        /*border-top: 1px solid red;
        border-left: 1px solid red;
        border-bottom: 1px solid red;*/
    }

    .tg-ra {
        text-align: right;
    }

    .tg-rab { 
        text-align: right;
        font-weight: bold;
    }

    .tg-lab {
        text-align: left;
        font-weight: bold;
    }

    .tg-plab {
        text-align: left;
        font-weight: bold;
        padding-left: 80px;
    }
    </style>              
EOF;
        
    }
    
    private function startTable()
    {
        $style = "class='tg'";
        $this->table .= $this->style;        
        $this->table .= "<table $style>";
    }
    
    private function endTable()
    {
        $this->table .= "</table>";
    }
     
    private function writeTitle($title)
    {
        $style = "class='tg-title'";
        $this->table .= "<tr><th $style colspan='7'>$title</th></tr>";
    }
    
    private function writeLine()
    {
        $this->table .= "<tr><td colspan='7'></td></tr>";
    }
    
    private function writeUnderline()
    {
        $style = "class='tg-undr'";
        $this->table .= "<tr><td $style colspan='7'></td></tr>";  
    }
    
    private function writeStudentHeader($name)
    {
        $style = "class='tg-sthd'";
        $this->table .= "<tr><td $style colspan='7'>$name</td></tr>";
    }
    
    private function writeCardHeaders()
    {
        $styleRab = "class='tg-rab'";
        $styleLab = "class='tg-lab'";
        
        $this->table .=         
        "<tr>" .
            "<td $styleLab>Source</td>" .
            "<td $styleLab>Contributor</td>" .
            "<td $styleRab>Amount</td>" .
            "<td $styleRab>Rebate</td>" .
            "<td $styleRab>Boosters Share</td>" .
            "<td $styleRab>Student Share</td>" .
            "<td></td>" .
        "</tr>";
    }
    
    private function writeHeader()
    {
        $styleRab = "class='tg-rab'";
                
        $this->table .=         
        "<tr>" .
            "<td $styleRab></td>" .
            "<td $styleRab></td>" .
            "<td $styleRab>Amount</td>" .
            "<td $styleRab>Rebate</td>" .
            "<td $styleRab>Boosters Share</td>" .
            "<td $styleRab>Student Share</td>" .
            "<td></td>" .
        "</tr>";
    }

    private function writeCardReload($card, $cardHolder, $amount, $rebatePercent)
    {
        $styleRa = "class='tg-ra'";
        
        $rebate = $amount * $rebatePercent;
        $amountStr = $this->numberToMoney($amount);
        $rebateStr = $this->numberToMoney($rebate);
        
        $this->table .=
        "<tr>" .
            "<td>$card</td>" .
            "<td>$cardHolder</td>" .
            "<td $styleRa>$amountStr</td>" .
            "<td $styleRa>$rebateStr</td>" .
            "<td></td>" .
            "<td></td>" .
            "<td></td>" .
        "</tr>";
    }
    
    private function writeNonStudentScrip($family)
    {
        $styleRa = "class='tg-ra'";
               
        $contributor = $family->getFullName();
        $amountStr = $this->numberToMoney($family->getTotalValue());
        $rebateStr = $this->numberToMoney($family->getTotalRebate());
        
        $this->table .=
        "<tr>" .
            "<td>ShopWithScrip</td>" .
            "<td>$contributor</td>" .
            "<td $styleRa>$amountStr</td>" .
            "<td $styleRa>$rebateStr</td>" .
            "<td></td>" .
            "<td></td>" .
            "<td></td>" .
        "</tr>";
    }
    
    private function writeScripFamily($family)
    {
        $styleRa = "class='tg-ra'";
                
        $contributor = $family->getFullName();
        $amount = $this->numberToMoney($family->getTotalValue());
        $rebate = $this->numberToMoney($family->getTotalRebate());
                
        $this->table .=
        "<tr>" .
            "<td>ShopWithScrip</td>" .
            "<td>$contributor</td>" .
            "<td $styleRa>$amount</td>" .
            "<td $styleRa>$rebate</td>" .
            "<td></td>" .
            "<td></td>" .
            "<td></td>" .
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
        
        $styleLab = "class='tg-lab'";
        $stylePlab = "class='tg-plab'";
        $styleRa = "class='tg-ra'";
                
        $this->table .=
        "<tr>" .
            "<td $stylePlab colspan='2'>$store</td>" .
            "<td $styleRa>$total</td>" .
            "<td $styleRa>$rebate</td>" .
            "<td $styleRa>$boostersShare</td>" .
            "<td $styleRa>$studentShare</td>" .
            "<td></td>" .
        "</tr>";
    }
    
    private function writeStudentTotal($name, $ksTotal, $swTotal, $scripTotalValue, $scripTotalRebate)
    {
        $studentTotal = $ksTotal + $swTotal + $scripTotalValue;
        $rebate = ($ksTotal * $this->ksRebatePercent) + ($swTotal * $this->swRebatePercent) + $scripTotalRebate;
        $boostersShare = $rebate * $this->boostersPercent;
        $studentShare = $rebate - $boostersShare;
                
        $studentTotalAmt = $this->numberToMoney($studentTotal);
        $rebateAmt = $this->numberToMoney($rebate);
        $boostersShareAmt = $this->numberToMoney($boostersShare);
        $studentShareAmt = $this->numberToMoney($studentShare);
        
        $styleLab = "class='tg-lab'";
        $stylePlab = "class='tg-plab'";
        $styleRa  = "class='tg-ra'";
        $styleB3sl = "class='tg-b3sl'";
        $styleR3sl = "class='tg-r3sl'";
        $styleB3sr = "class='tg-b3sr'";
        $styleR3sr = "class='tg-r3sr'";
               
        $this->table .=
        "<tr>" .
            "<td $stylePlab colspan='2'>Student total</td>" .
            "<td $styleRa>$studentTotalAmt</td>" .
            "<td $styleRa>$rebateAmt</td>" .
            "<td $styleRa>$boostersShareAmt</td>";
        
        if ($studentShare < 0) {
            $this->table .=               
            "<td $styleR3sl>$studentShareAmt</td>" .
            "<td $styleR3sr>$name</td>";
        }
        else {
            $this->table .= 
            "<td $styleB3sl>$studentShareAmt</td>" .
            "<td $styleB3sr>$name</td>" ;            
        }
        $this->table .= "</tr>";
        
        $this->writeLine();
    }
    
    private function writeNonStudentTotal($ksTotal, $swTotal, $scripTotalValue, $scripTotalRebate)
    {
        $total = $ksTotal + $swTotal + $scripTotalValue;
        $rebate = ($ksTotal * $this->ksRebatePercent) + 
                  ($swTotal * $this->swRebatePercent) + $scripTotalRebate;
        $boostersShare = $rebate;
        $studentShare = $rebate - $boostersShare;
                
        $totalAmt = $this->numberToMoney($total);
        $rebateAmt = $this->numberToMoney($rebate);
        $boostersShareAmt = $this->numberToMoney($boostersShare);
        $studentShareAmt = $this->numberToMoney($studentShare);
        
        $stylePlab = "class='tg-plab'";
        $styleRa = "class='tg-ra'";
                
        $this->table .=
        "<tr>" .
            "<td $stylePlab colspan='2'>Total</td>" .
            "<td $styleRa>$totalAmt</td>" .
            "<td $styleRa>$rebateAmt</td>" .
            "<td $styleRa>$boostersShareAmt</td>" .
            "<td $styleRa>$studentShareAmt</td>" .
            "<td></td>" .
        "</tr>";
                
        $this->writeLine();
    }
    
    private function writeStudentFunds()
    {
        $this->writeTitle("Funds per Student");

        foreach($this->students as $student)
        {
            $name = $student["first"] . " " . $student["last"];
            $ksCardCount = count($student["ksCards"]);
            $swCardCount = count($student["swCards"]);
            $ksCardsTotal = $student["ksCardsTotal"];
            $swCardsTotal = $student["swCardsTotal"];
            $scripTotalValue = 0;
            $scripTotalRebate = 0;
            
            $this->writeStudentHeader($name);
            $this->writeCardHeaders();
            
            foreach($student["ksCards"] as $cardData)
            {
                $this->writeCardReload($cardData["cardNumber"], $cardData["cardHolder"], $cardData["total"], $this->ksRebatePercent);
            }
            
            foreach($student["swCards"] as $cardData)
            {
                $this->writeCardReload($cardData["cardNumber"], $cardData["cardHolder"], $cardData["total"], $this->swRebatePercent);
            }
            
            foreach($student["scripFamilies"] as $family)
            {
                $scripTotalValue += $family->getTotalValue();
                $scripTotalRebate += $family->getTotalRebate();
                $this->writeScripFamily($family);
            }
            
            $this->writeStudentTotal($name, $ksCardsTotal, $swCardsTotal, $scripTotalValue, $scripTotalRebate);
        }
        $this->writeUnderline();
        $this->writeLine();
    }
      
    private function calculateTotals() 
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
        
        foreach($this->scripFamilies as $family) {
            if ($family->getStudentId() === NULL) {
                $this->nonStudentScripFamilies[] = $family;
                $this->nonStudentScripTotalValue += $family->getTotalValue();
                $this->nonStudentScripTotalRebate += $family->getTotalRebate();
            } else {
                $this->studentScripTotalValue += $family->getTotalValue();
                $this->studentScripTotalRebate += $family->getTotalRebate();
            }
        }
            
    }
    
    private function writeNonStudentFunds()
    {   
        if ($this->ksNumUnsoldCards > 0 || $this->swNumUnsoldCards > 0 || 
            count($this->nonStudentScripFamilies) > 0) {
            
            $this->writeTitle("Funds Unassociated with a Student");           
            $this->writeCardHeaders();
            
            foreach($this->ksUnsoldCards as $cardData) {
                $card = $cardData["cardNumber"];
                $cardHolder = $cardData["cardHolder"];
                $amount = $cardData["total"];
                $this->writeCardReload($card, $cardHolder, $amount, $this->ksRebatePercent);
            }
            
            foreach($this->swUnsoldCards as $cardData) {
                $card = $cardData["cardNumber"];
                $cardHolder = $cardData["cardHolder"];
                $amount = $cardData["total"];
                $this->writeCardReload($card, $cardHolder, $amount, $this->swRebatePercent);
            }
            
            foreach($this->nonStudentScripFamilies as $family) {
                $this->writeNonStudentScrip($family);
            }
            
            $this->writeNonStudentTotal($this->ksUnsoldTotal, $this->swUnsoldTotal,
                    $this->nonStudentScripTotalValue, $this->nonStudentScripTotalRebate);
            
            $this->writeUnderline();
            $this->writeLine();
        }
    }
    
    private function writeOverallGroceryTotals() {
        $this->writeTitle("Total Funds");
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
        
        $scripTotal = $this->studentScripTotalValue + $this->nonStudentScripTotalValue;
        $scripRebate = $this->studentScripTotalRebate + $this->nonStudentScripTotalRebate;
        $scripBoostersShare = $this->nonStudentScripTotalRebate + 
                              ($this->studentScripTotalRebate * $this->boostersPercent);
        $scripStudentShare = $scripRebate - $scripBoostersShare;
        
        $scripTotalAmt = $this->numberToMoney($scripTotal);
        $scripRebateAmt = $this->numberToMoney($scripRebate);
        $scripBoostersShareAmt = $this->numberToMoney($scripBoostersShare);
        $scripStudentShareAmt = $this->numberToMoney($scripStudentShare);
        
        $this->writeStoreCardsTotalValues("ShopWithScrip", $scripTotalAmt, $scripRebateAmt,
                $scripBoostersShareAmt, $scripStudentShareAmt);
        
        $total = $ksTotal + $swTotal + $scripTotal;
        $rebate = $ksRebate + $swRebate + $scripRebate;
        $boostersShare = $ksBoostersShare + $swBoostersShare + $scripBoostersShare;
        $studentShare = $ksStudentShare + $swStudentShare + $scripStudentShare;
        
        $totalAmt = $this->numberToMoney($total);
        $rebateAmt = $this->numberToMoney($rebate);
        $boostersShareAmt = $this->numberToMoney($boostersShare);
        $studentShareAmt = $this->numberToMoney($studentShare);
        
        $this->writeStoreCardsTotalValues("Total", $totalAmt, $rebateAmt,
                $boostersShareAmt, $studentShareAmt);
    }
    
    private function writeSourceFileNames() 
    {
        $this->writeTitle("Source Files");
        
        $styleLab = "class='tg-lab'";
        $stylePlab = "class='tg-plab'";
        $styleRa = "class='tg-ra'";
                
        $donorNames = array("King Soopers", "Safeway", "ShopWithScrip");
        
        for ($i = 0; $i <= 2; $i++) 
        {            
            $filename = $this->srcFileNames[$i];
            $this->table .=
            "<tr>" .
                "<td></td>" .
                "<td $stylePlab colspan='2'>$donorNames[$i]</td>" .
                "<td></td>" .
                "<td colspan='3'>$filename</td>" .
            "</tr>";
        }
        
        $this->writeLine();
    }
    
    private function buildTable()
    {
        $this->table = "";
        $this->startTable();
        $this->writeSourceFileNames();
        $this->writeStudentFunds();
        $this->writeNonStudentFunds();
        $this->writeOverallGroceryTotals();
        $this->endTable();
    }
    
    public function getTable()
    {
        $result = null;

        if ($this->tableForHtml == null) 
        {
            $this->buildTable();
            $this->tableForHtml = $this->table;
        }
        $result = $this->tableForHtml; 
        return $result;
    }
    
}
