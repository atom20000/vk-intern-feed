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
        if (!preg_match('/^\+?[0-9]+$/', $phoneNumber))
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
        return !is_numeric($phoneId)
            ? false
            : $this->executeStatement(
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

    /**
     * Update review rating.
     *
     * @param string $reviewId
     * @param int $delta
     * Value to adjust rating score:
     * increment (upvote) or decrement (downvote).
     * @return array|false
     * Updated review entry or false if review was not found.
     */
    public function adjustReviewRating(string $reviewId, int $delta)
    {
        if (!is_numeric($reviewId))
        {
            return false;
        }

        $querySelectReviews = <<<SQL
            SELECT *, COUNT(*) AS count
            FROM reviews
            WHERE id = :reviewId
        SQL;

        $reviewEntry = $this->executeStatement(
            $querySelectReviews,
            [
                ':reviewId' => $reviewId
            ]
        )->fetch();

        if ($reviewEntry['id'] === null)
        {
            return false;
        }

        $this->executeStatement(
            <<<SQL
                UPDATE reviews
                SET rating = rating + (:delta)
                WHERE id = :reviewId
                SQL,
            [
                ':reviewId' => $reviewId,
                ':delta' => $delta
            ]
        );

        return $this->executeStatement(
            $querySelectReviews,
            [
                ':reviewId' => $reviewId
            ]
        )->fetch(PDO::FETCH_ASSOC);
    }
}
