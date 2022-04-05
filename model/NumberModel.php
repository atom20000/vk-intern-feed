<?php

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
        $phoneNumber = str_replace(['(', ')', '-', ' '], '', $phoneNumber);

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
     * Search for a phone number beginning with a pattern.
     *
     * @param string $phonePattern
     * Beginning of a phone number.
     * @return array|false
     * Array of phone numbers (['id'] and ['number']) on successful search,
     * or false if the number is invalid.
     */
    public function matchPhone(string $phonePattern)
    {
        // remove unnecessary characters
        $phonePattern = str_replace(['(', ')', '-', ' '], '', $phonePattern);

        // phone number must contain digits and `+` only
        if (!preg_match('/\+?[0-9]+/', $phonePattern))
        {
            return false;
        }

        $foundPhones = $this->executeStatement(
            <<<SQL
                SELECT id, number
                FROM phones
                WHERE number LIKE :pattern
                SQL,
            [
                ':pattern' => $phonePattern . '%'
            ]
        )->fetchAll();

        // empty array returned
        if (!$foundPhones)
        {
            return false;
        }

        return $foundPhones;
    }

    /**
     * Get ID for a given phone number.
     *
     * @param string $phoneNumber
     * @return string|false
     * Given phone ID if exists,
     * or false if phone not found.
     */
    public function getPhoneId(string $phoneNumber)
    {
        // remove unnecessary characters
        $phoneNumber = str_replace(['(', ')', '-', ' '], '', $phoneNumber);

        // phone number must contain digits and `+` only
        if (!preg_match('/\+?[0-9]+/', $phoneNumber))
        {
            return false;
        }

        // check if number exists
        $countResult = $this->executeStatement(
            <<<SQL
                SELECT id, COUNT(*) AS count
                FROM phones
                WHERE number = :phone
                SQL,
            [
                ':phone' => $phoneNumber
            ]
        )->fetch();

        return $countResult['id'] === null ? false : $countResult['id'];
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
