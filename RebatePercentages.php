<?php


/**
 * Description of RebatePercentages
 *
 * @author BickfordC
 */
class RebatePercentages {
    
    private $ksRebatePercentage;
    private $swRebatePercentage;
    private $boostersPercentage;
    
    function __construct($ksSales, $swSales)
    {
        $this->calculatePercentages($ksSales, $swSales);
    }
    
    private function calculatePercentages($ksSales, $swSales)
    {
        $this->boostersPercentage = 0.40;
        
        if ($ksSales >= 5000) {
            $this->ksRebatePercentage = 0.05;
        } 
        elseif ($ksSales >= 2500) {
            $this->ksRebatePercentage = 0.04;
        }
        else {
            $this->ksRebatePercentage = 0.03;
        }
        
        if ($swSales >= 5000) {
            $this->swRebatePercentage = 0.05;
        } 
        elseif ($swSales >= 2500) {
            $this->swRebatePercentage = 0.04;
        }
        else {
            $this->swRebatePercentage = 0.03;
        }
    }
    
    public function getKsRebatePercentage() {
        return $this->ksRebatePercentage;
    }

    public function getSwRebatePercentage() {
        return $this->swRebatePercentage;
    }
    
    public function getBoostersPercentage() {
        return $this->boostersPercentage;
    }
}
