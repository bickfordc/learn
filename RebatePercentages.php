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
        // TODO look up rates from database table based on sales
        
        $this->ksRebatePercentage = 0.05;
        $this->swRebatePercentage = 0.05;
        $this->boostersPercentage = 0.40;
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
