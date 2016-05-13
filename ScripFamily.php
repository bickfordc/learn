<?php

require_once "functions.php";

/**
 * A ScripFamily is an account on shopwithscrip.com, uniqely identified by a
 * first and last name. It may or may not direct money to a student.
 * Funds are tracked by a number of orders, each of which has a value and a lesser
 * cost. The difference between value and cost is the rebate, which goes to
 * Boosters, with a share to the student if there is one.
 *
 * @author BickfordC
 */
class ScripFamily {
     
    private $firstName;
    private $lastName;
    private $orders = array();
    private $totalValue = 0;
    private $totalCost = 0 ;
    private $totalRebate = 0;
    private $studentId = null;
    private $studentFirst;
    private $studentLast;
    
    function __construct($firstName, $lastName) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        
        $this->getStudent($firstName, $lastName);
    }
    
    private function getStudent($scripFirst, $scripLast) {
        
        $student = null;
        
        $result = queryPostgres(
           "SELECT * FROM student_scrip_families WHERE scrip_first=$1 AND scrip_last=$2", 
           array($scripFirst, $scripLast));
        
        if (pg_num_rows($result) > 0) {
            
            $row = pg_fetch_array($result);
            $this->studentId = $row["student"];
            
            $result = queryPostgres(
                "SELECT * FROM students WHERE id=$1", array($this->studentId));
            $row = pg_fetch_array($result);
            $this->studentFirst = $row["first"];
            $this->studentLast = $row["last"];
        }
    }
    
    public function addOrder($value, $cost) {
        
        $order = array("value" => $value, "cost" => $cost);
        $this->orders[] = $order;
        
        $this->totalValue  += $value;
        $this->totalCost   += $cost;
        $this->totalRebate += $value - $cost;
    }
    
    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getFullName() {
        return $this->firstName . " " . $this->lastName;
    }
    
    public function getOrders() {
        return $this->orders;
    }

    public function getTotalValue() {
        return $this->totalValue;
    }

    public function getTotalCost() {
        return $this->totalCost;
    }

    public function getTotalRebate() {
        return $this->totalRebate;
    }

    public function getNumOrders() {
        return count($this->orders);
    }
    
    public function getStudentId() {
        return $this->studentId;
    }
    
    public function getStudentFirstName() {
        return $this->studentFirst;
    }
    
    public function getStudentLastName() {
        return $this->studentLast;
    }
}
