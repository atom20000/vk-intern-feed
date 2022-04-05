<?php

/**
 * Implementations for everything which can be done with reviews.
 */
class ReviewModel extends Database
{
    // TODO: upvote and downvote review

    /**
     * Submit a review for a given phone number.
     * NOTE: the number for a review must be present in the database.
     *
     * @param string $phoneNumber
     * @param string $reviewText
     * @param string $author
     * @return string|false
     */
    public function submitReview(string $phoneNumber, string $reviewText, string $author = 'anonymous')
    {
        // remove unnecessary characters
        $phoneNumber = str_replace(['(', ')', '-', ' '], '', $phoneNumber);

        // phone number must contain digits and `+` only
        if (!preg_match('/\+?[0-9]+/', $phoneNumber))
        {
            return false;
        }

        // get id of provided number
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

//        // number is not inserted - need to insert and get its id first
//        if ($countResult['id'] === null)
//        {
//            $this->executeStatement(
//                <<<SQL
//                    INSERT INTO phones (number)
//                    VALUES (:phone)
//                    SQL,
//                [
//                    ':phone' => $phoneNumber
//                ]
//            );
//
//            $countResult['id'] = $this->executeStatement(
//                <<<SQL
//                    SELECT id
//                    FROM phones
//                    WHERE number = :phone
//                    SQL,
//                [
//                    ':phone' => $phoneNumber
//                ]
//            )->fetch()['id'];
//        }

        $this->executeStatement(
            <<<SQL
                INSERT INTO reviews (num_id, body, author)
                VALUES (:numId, :body, :author)
                SQL,
            [
                ':numId' => $countResult['id'],
                ':body' => $reviewText,
                ':author' => $author
            ]
        );

        // insert successful - return inserted id
        return $countResult['id'];
    }

    /**
     * Get reviews for a given number ID.
     *
     * @param string $phoneId
     * @return array|false
     */
    public function getReviews(string $phoneId)
    {
        return $this->executeStatement(
            <<<SQL
                SELECT id, body, author, rating
                FROM reviews
                WHERE num_id = :phoneId
                SQL,
            [
                ':phoneId' => $phoneId
            ]
        )->fetchAll();
    }
}
