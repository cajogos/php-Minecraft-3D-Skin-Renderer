<?php

/**
 * Returns timing in microseconds - used to calculate time taken to process images
 * @return float
 */
function microtime_float()
{
    $micro = explode(' ', microtime());
    return (float)$micro[0] + (float)$micro[1];
}