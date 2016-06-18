<?php

//include the information needed for the connection to database server. 
// 
require_once 'functions.php';

// Which operation was requested; add, edit, or delete?
$op = $_POST['oper'];    
if ($op === "add") {
    addStudent();
} elseif ($op === "edit") {
    editStudent();
} elseif ($op === "del") {
    delStudent();
}

function addStudent() {
    $first = sanitizeString($_POST['first']);
    $last = sanitizeString($_POST['last']);
    $active = sanitizeString($_POST['active']);
    
    $result = queryPostgres("INSERT INTO students (first, last, active) VALUES ($1, $2, $3)", 
            array($first, $last, $active)); 
}

function editStudent() {
    $first = sanitizeString($_POST['first']);
    $last = sanitizeString($_POST['last']);
    $active = sanitizeString($_POST['active']);
    $id = sanitizeString($_POST['id']);
    
    $result = queryPostgres("UPDATE students SET first=$1, last=$2, active=$3 WHERE id=$4", 
            array($first, $last, $active, $id)); 
}

function delStudent() {
    $id = sanitizeString($_POST['id']);
    
    $result = queryPostgres("DELETE FROM students WHERE id=$1", 
            array($id)); 
}