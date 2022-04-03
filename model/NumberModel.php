<?php

//require_once PROJECT_ROOT . "/model/Database.php";

/**
 * Implementations for everything which can be done with phone numbers.
 */
class NumberModel extends Database
{
    /**
     * Get country code for a given phone number.
     * Available countries are:
     * - RU (Russia)
     * - US (USA)
     * - CH (China)
     * - MX (Mexico)
     *
     * // TODO: errors, international numbers, etc.
     *
     * @param string $phoneNumber
     * @return string|false
     */
    public function getCountryCode(string $phoneNumber)
    {
        // TODO
        return "TEST_t3st";
    }
}
