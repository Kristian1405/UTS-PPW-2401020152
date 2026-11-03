<?php

if (! function_exists('statusBerat')) {
    function statusBerat(int $berat): string
    {
        return $berat >= 10 ? 'Besar' : 'Reguler';
    }
}
