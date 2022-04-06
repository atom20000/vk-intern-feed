<?php

class ReviewController extends BaseController
{
    /**
     * Endpoint `/review/submit_review` - add review for a given phone number.
     * Authorization and authentication is NOT implemented in this project.
     * Instead, the review author is parsed directly from the request body
     * (key 'username' in JSON).
     */
    public function submit_reviewAction()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $params = $this->getQueryStringParams();
        $response = [
            'error' => true
        ];
        $responseHeaders = [];
        $contentType = isset($_SERVER['CONTENT_TYPE'])
            ? trim($_SERVER['CONTENT_TYPE'])
            : '';

        if ((strtoupper($method) === 'POST')
            && (strcasecmp($contentType, 'application/json') >= 0))
        {
            $requestContent = file_get_contents('php://input');
            $json = json_decode($requestContent, true);

            if (is_array($json)
                && isset($params['phone_number'])
                && $params['phone_number']
                && isset($json['review_text'])
                && $json['review_text'])
            {
                $reviewModel = new ReviewModel();

                try
                {
                    if ($reviewModel->submitReview(
                        $params['phone_number'],
                        $json['review_text'],
                        (isset($json['author']) && $json['author'])
                            ? $json['author']
                            : 'anonymous'
                    ))
                    {
                        $response['error'] = false;
                        $response['result'] = [
                            'phone_number' => $params['phone_number'],
                            'submitted_review' => $json['review_text']
                        ];
                        $responseHeaders[] = 'HTTP/1.1 201 Created';
                    }
                    else
                    {
                        $response['description'] = 'Incorrect phone format';
                        $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
                    }
                }
                catch (UnexpectedValueException $ex) // thrown during SQL statement execution
                {
                    $response['description'] = 'Internal Server Error';
                    $responseHeaders[] = 'HTTP/1.1 500 Internal Server Error';
                    $responseHeaders[] = 'Content-Type: application/json';

                    $this->sendOutput($response, $responseHeaders);
                }
            }
            else
            {
                $response['description'] = <<<TXT
                    The phone_number parameter
                    and non-empty review_text in JSON body must be provided
                    TXT;
                $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
            }
        }
        else
        {
            $response['description'] = 'HTTP method or Content-Type is not supported';
            $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
        }

        $responseHeaders[] = 'Content-Type: application/json';

        $this->sendOutput(json_encode($response), $responseHeaders);
    }

    /**
     * Endpoint `/review/list_reviews` - get reviews for a given phone number.
     */
    public function list_reviewsAction()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $params = $this->getQueryStringParams();
        $response = [
            'error' => true
        ];
        $responseHeaders = [];

        if (strtoupper($method) === 'GET')
        {
            if (isset($params['phone_number']) && $params['phone_number'])
            {
                $numberModel = new NumberModel();
                $reviewModel = new ReviewModel();
                $phoneId = null;
                $reviews = null;

                try
                {
                    $phoneId = $numberModel->getPhoneId($params['phone_number']);
                }
                catch (UnexpectedValueException $ex)
                {
                    $response['description'] = 'Internal Server Error';
                    $responseHeaders[] = 'HTTP/1.1 500 Internal Server Error';
                    $responseHeaders[] = 'Content-Type: application/json';

                    $this->sendOutput($response, $responseHeaders);
                }

                if ($phoneId)
                {
                    try
                    {
                        $reviews = $reviewModel->getReviews($phoneId);
                    }
                    catch (UnexpectedValueException $ex)
                    {
                        $response['description'] = 'Internal Server Error';
                        $responseHeaders[] = 'HTTP/1.1 500 Internal Server Error';
                        $responseHeaders[] = 'Content-Type: application/json';

                        $this->sendOutput($response, $responseHeaders);
                    }

                    if ($reviews)
                    {
                        $response['error'] = false;
                        $response['result'] = [
                            'phone_number' => $params['phone_number'],
                            'reviews' => $reviews
                        ];
                        $responseHeaders[] = 'HTTP/1.1 200 OK';
                    }
                    else
                    {
                        $response['description'] = 'Incorrect phone format';
                        $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
                    }
                }
                else
                {
                    $response['error'] = false;
                    $response['result'] = [
                        'phone_number' => $params['phone_number'],
                        'reviews' => []
                    ];
                    $responseHeaders[] = 'HTTP/1.1 200 OK';

//                    $response['description'] = 'Incorrect phone format, phone was not found';
//                    $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
                }
            }
            else
            {
                $response['description'] = 'The phone_number parameter must be provided';
                $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
            }
        }
        else
        {
            $response['description'] = 'HTTP method is not supported';
            $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
        }

        $responseHeaders[] = 'Content-Type: application/json';

        $this->sendOutput(json_encode($response), $responseHeaders);
    }

    /**
     * Endpoint `/review/upvote` - increment review rating.
     */
    public function upvoteAction()
    {
        $this->adjustRating(1);
    }

    /**
     * Endpoint `/review/downvote` - decrement review rating.
     */
    public function downvoteAction()
    {
        $this->adjustRating(-1);
    }

    private function adjustRating(int $delta)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $params = $this->getQueryStringParams();
        $response = [
            'error' => true
        ];
        $responseHeaders = [];
        $contentType = isset($_SERVER['CONTENT_TYPE'])
            ? trim($_SERVER['CONTENT_TYPE'])
            : '';

        if ((strtoupper($method) === 'POST')
            && (strcasecmp($contentType, 'application/json') >= 0))
        {
            $requestContent = file_get_contents('php://input');
            $json = json_decode($requestContent, true);

            if (is_array($json)
                && isset($params['review_id'])
                && $params['review_id']
                && isset($json['username'])
                && $json['username'])
            {
                $reviewModel = new ReviewModel();
                $updatedReview = null;

                try
                {
                    $updatedReview = $reviewModel->adjustReviewRating($params['review_id'], $delta);
                }
                catch (UnexpectedValueException $ex)
                {
                    $response['description'] = 'Internal Server Error';
                    $responseHeaders[] = 'HTTP/1.1 500 Internal Server Error';
                    $responseHeaders[] = 'Content-Type: application/json';

                    $this->sendOutput($response, $responseHeaders);
                }

                if ($updatedReview)
                {
                    $response['error'] = false;
                    $response['result'] = [
                        'updated_review' => $updatedReview
                    ];
                    $responseHeaders[] = 'HTTP/1.1 200 OK';
                }
                else
                {
                    $response['description'] = 'Incorrect review id, review not found';
                    $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
                }
            }
            else
            {
                $response['description'] = 'The review_id parameter and non-empty username in JSON body must be provided';
                $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
            }
        }
        else
        {
            $response['description'] = 'HTTP method or Content-Type is not supported';
            $responseHeaders[] = 'HTTP/1.1 400 Bad Request';
        }

        $responseHeaders[] = 'Content-Type: application/json';

        $this->sendOutput(json_encode($response), $responseHeaders);
    }
}
