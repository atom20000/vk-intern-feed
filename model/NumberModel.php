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
     * - `+7` - RU (Russia)
     * - `+1` - US (USA)
     * - `+86` - CH (China)
     * - `+52`, `+1905` - MX (Mexico)
     *
     * International numbers must start with `+`.
     * Otherwise, the number is considered russian.
     *
     * @param string $phoneNumber
     * @return string|false
     */
    public function getCountryCode(string $phoneNumber)
    {
        // remove unnecessary characters
        str_replace(['(', ')', '-', ' '], '', $phoneNumber);

        // phone number must contain digits and `+` only
        if (!preg_match('/\+?[0-9]+/', $phoneNumber))
        {
            return false;
        }

        $strRu = '+7';
        $strUs = '+1';
        $strCh = '+86';
        $strMx1 = '+52';
        $strMx2 = '+1905';

        // international number
        if ($this->strStartsWith($phoneNumber, '+'))
        {
            if ($this->strStartsWith($phoneNumber, $strRu))
            {
                return 'RU';
            }

            if ($this->strStartsWith($phoneNumber, $strUs))
            {
                if ($this->strStartsWith($phoneNumber, $strMx2))
                    return 'MX';
                else
                    return 'US';
            }

            if ($this->strStartsWith($phoneNumber, $strCh))
            {
                return 'CH';
            }

            if ($this->strStartsWith($phoneNumber, $strMx1))
            {
                return 'MX';
            }

        }
        else
        {
            return 'RU';
        }

        // unknown number code or format
        return false;
    }

    /**
     * Check if string starts with a specific pattern.
     *
     * @param string $subject
     * @param string $pattern
     * @return bool
     */
    private function strStartsWith(string $subject, string $pattern): bool
    {
        return substr($subject, 0, strlen($pattern)) === $pattern;
    }
}
