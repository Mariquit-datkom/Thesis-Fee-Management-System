<?php
require_once 'syncCache.php';

function getStudentFees($studentId) {
    $allFees = getCachedData('fees');
    
    if (isset($allFees[$studentId])) {
        return ['fees' => $allFees[$studentId], 'exists' => true];
    }
    
    // Check if student exists at all in info cache to give better feedback
    $allInfo = getCachedData('info');
    return ['fees' => [], 'exists' => isset($allInfo[$studentId])];
}