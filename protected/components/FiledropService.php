<?php

/**
 * Service to manage the creation and housekeeping of Filedrop accounts
 * used for author to upload files related to submitted manuscript
 *
 * createAccount: create a filedrop account
 * getAccount: retrieve info about existing filedrop account
 * emailInstructions: save instructions in filedrop account and send email
 *
 *
 * @property \TokenService $tokenSrv we need the service of JWT token generation
 * @property \GuzzleHttp\Client $webClient the web agent for making REST call
 * @property \User $requester the logged in user
 * @property string $identifier DOI of the dataset for which to create a filedrop account
 * @property string $instructions text to sent authors for uploading data
 * @property DatasetDAO $dataset Instance of DatasetDAO for working with dataset resultsets
 * @property boolean $dryRunMode whether or not to simulate final resource changes
 * @property \Lcobucci\JWT\Token $token generated for multiple call to the api per session
 *
 * @author Rija Menage <rija+git@cinecinetique.com>
 * @license GPL-3.0
 */

use GuzzleHttp\Middleware;
use Ramsey\Uuid\Uuid;
use yii\validators\EmailValidator;
use backend\models\FiledropAccount;

class FiledropService extends yii\base\Component
{
    const MOCKUP_POSSIBLE_VALIDITY  = [1,3,6];
    /**
     * {@inheritdoc}
     */
    public $tokenSrv;
    /**
     * {@inheritdoc}
     */
    public $webClient;
    /**
     * {@inheritdoc}
     */
    public $requester;
    /**
     * {@inheritdoc}
     */
    public $identifier;
    /**
     * {@inheritdoc}
     */
    public $instructions;
    /**
     * {@inheritdoc}
     */
    public $dataset;
    /**
     * {@inheritdoc}
     */
    public $dryRunMode;

    /**
     * {@inheritdoc}
     */
    public $token;

    /**
     * Make HTTP POST to File Upload Wizard to create Filedrop account
     *
     * @return array||null if successfully created, the filedrop account is returned as array, null otherwise
     */
    public function createAccount(): ?array
    {
        $api_endpoint = "http://fuw-admin-api/filedrop-accounts";

        if ("admin" !== $this->requester->role) {
            Yii::log("The requesting user doesn't have admin role", "error");
            return null;
        }
        // 'postID=:postID', array(':postID'=>10)
        $dataset = Dataset::model()->find('identifier=:doi', [":doi" => $this->identifier]) ;
        if (!isset($dataset) || "AssigningFTPbox" !== $dataset->upload_status) {
            Yii::log("Upload status required for DOI {$this->identifier}: AssigningFTPbox", "error");
            Yii::log("Gotten: {$dataset->upload_status}", "error");
            return null;
        }
        $token = $this->tokenSrv->generateTokenForUser($this->requester->email);

        try {
            $response = $this->webClient->request('POST', $api_endpoint, [
                                    'headers' => [
                                        'Authorization' => "Bearer $token",
                                    ],
                                    'form_params' => [
                                        'doi' => $this->identifier,//TODO:check it's right status
                                        'dryRunMode' => $this->dryRunMode,
                                    ],
                                    'connect_timeout' => 5,
                                ]);
            if (201 === $response->getStatusCode()) {
                $this->dataset->transitionStatus("AssigningFTPbox", "UserUploadingData", $this->instructions);
                return json_decode($response->getBody(), true);
            }
        } catch (RequestException $e) {
            Yii::log(Psr7\str($e->getRequest()), "error");
            if ($e->hasResponse()) {
                Yii::log(Psr7\str($e->getResponse()), "error");
            }
        }
        return null;
    }


    /**
     * Make HTTP PUT to File Upload Wizard to save instructions
     *
     * @param int $filedrop_id internal id of a filedrop account to update
     * @param string $instructions email content
     *
     * @return bool whether the call has been made and succeed or not
     */
    public function saveInstructions(int $filedrop_id, string $instructions): bool
    {

        if (!$instructions) {
            return false;
        }

        $api_endpoint = "http://fuw-admin-api/filedrop-accounts/$filedrop_id";

        // reuse token to avoid "You must unsign before making changes" error
        // when multiple API calls in same session
        $this->token = $this->token ?? $this->tokenSrv->generateTokenForUser($this->requester->email);

        try {
            $response = $this->webClient->request('PUT', $api_endpoint, [
                                    'headers' => [
                                        'Authorization' => "Bearer " . $this->token,
                                    ],
                                    'form_params' => [
                                        'doi' => $this->identifier,
                                        'instructions' => $instructions,
                                    ],
                                    'connect_timeout' => 5,
                                ]);
            if (200 === $response->getStatusCode()) {
                return true;
            }
        } catch (RequestException $e) {
            Yii::log(Psr7\str($e->getRequest()), "error");
            if ($e->hasResponse()) {
                Yii::log(Psr7\str($e->getResponse()), "error");
            }
        }
        return false;
    }

    /**
     * Make HTTP PUT to File Upload Wizard to save and send email instructions
     *
     * @param int $filedrop_id internal id of a filedrop account to update
     * @param string $recipient whom to send the email
     * @param string $subject subject to use for the email to be sent
     * @param string $instructions email content
     *
     * @return ?array whether the call has been made and succeed or not. If successful a response array is returned.
     */
    public function emailInstructions(int $filedrop_id, string $recipient, string $subject, string $instructions): ?array
    {


        if (!$recipient) {
            return null;
        }

        if (!$instructions) {
            return null;
        }

        if (!$subject) {
            return null;
        }

        $api_endpoint = "http://fuw-admin-api/filedrop-accounts/$filedrop_id";

        // reuse token to avoid "You must unsign before making changes" error
        // when multiple API calls in same session
        $this->token = $this->token ?? $this->tokenSrv->generateTokenForUser($this->requester->email);

        try {
            $response = $this->webClient->request('PUT', $api_endpoint, [
                                    'headers' => [
                                        'Authorization' => "Bearer " . $this->token,
                                    ],
                                    'form_params' => [
                                        'doi' => $this->identifier,
                                        'subject' => $subject,
                                        'instructions' => $instructions,
                                        'to' => $recipient,
                                        'send' => true,
                                    ],
                                    'connect_timeout' => 5,
                                ]);
            if (200 === $response->getStatusCode()) {
                //convert returned json to a PHP array so we can work with it
                $responseData = json_decode($response->getBody(), true);

                // add author to fuw users so they can authenticate using JWT token when they annotate files
                $authorUserName = str_replace(" ", "", strtolower($this->dataset->getSubmitter()->getFullName()));
                $authorUserEmail = $this->dataset->getSubmitter()->email;
                $userData = $this->tokenSrv->createUser(
                    $this->token,
                    $this->webClient,
                    $authorUserName,
                    $authorUserEmail
                );


                $responseData['authorUserId'] = $userData['id'];
                $responseData['authorUserName'] = $userData['username'];
                $responseData['authorUserEmail'] = $userData['email'];
                return $responseData;
            }
        } catch (RequestException $e) {
            Yii::log(Psr7\str($e->getRequest()), "error");
            if ($e->hasResponse()) {
                Yii::log(Psr7\str($e->getResponse()), "error");
            }
        }
        return null;
    }

/**
     * Make HTTP GET to File Upload Wizard to retrieve an active filedrop account by its DOI
     *
     * @param string $doi DOI of the files to return
     *
     * @return array||null return an array of uploads or null if not found
     */
    public function getAccounts(string $doi): ?array
    {
        $api_endpoint = "http://fuw-admin-api/filedrop-accounts";

        // reuse token to avoid "You must unsign before making changes" error
        // when multiple API calls in same session
        $this->token = $this->token ?? $this->tokenSrv->generateTokenForUser($this->requester->email);

        try {
            $response = $this->webClient->request('GET', $api_endpoint, [
                                    'headers' => [
                                        'Authorization' => "Bearer " . $this->token,
                                    ],
                                    'query' => [ 'filter[doi]' => $doi,  'filter[status]' => FiledropAccount::STATUS_ACTIVE ],
                                    'connect_timeout' => 5,
                                ]);
            if (200 === $response->getStatusCode()) {
                // Yii::log($response->getBody(),'info');
                $accounts =  json_decode($response->getBody(), true);
                return $accounts[0];
            }
        } catch (RequestException $e) {
            Yii::log(Psr7\str($e->getRequest()), "error");
            if ($e->hasResponse()) {
                Yii::log(Psr7\str($e->getResponse()), "error");
            }
        }
        return null;
    }

    /**
     * Make HTTP GET to File Upload Wizard to retrieve Filedrop account
     *
     * @param int $filedrop_id internal id of filedrop account
     *
     * @return array||null return an array of attributes or null if not found
     */
    public function getAccountDetails(int $filedrop_id): ?array
    {
        $api_endpoint = "http://fuw-admin-api/filedrop-accounts/$filedrop_id";

        // reuse token to avoid "You must unsign before making changes" error
        // when multiple API calls in same session
        $this->token = $this->token ?? $this->tokenSrv->generateTokenForUser($this->requester->email);

        try {
            $response = $this->webClient->request('GET', $api_endpoint, [
                                    'headers' => [
                                        'Authorization' => "Bearer " . $this->token,
                                    ],
                                    'connect_timeout' => 5,
                                ]);
            if (200 === $response->getStatusCode()) {
                return json_decode($response->getBody(), true);
            }
        } catch (RequestException $e) {
            Yii::log(Psr7\str($e->getRequest()), "error");
            if ($e->hasResponse()) {
                Yii::log(Psr7\str($e->getResponse()), "error");
            }
        }
        return null;
    }

/**
     * Make HTTP POST to File Upload Wizard to create a new mockup_url record
     *
     * @param TokenService $tokenService token generator service
     * @param string $reviewerEmail the email of the reviewer the url is made for
     * @param int $monthsOfValidity How many month (1,3, or 6) the url is to be valid
     *
     * @return ?array UUID used as unique url fragment to the mockup url, and userId of corresponding new user record
     */
    public function makeMockupUrl(TokenService $tokenMaker, string $reviewerEmail, int $monthsOfValidity): ?array
    {
        if (!in_array($monthsOfValidity, self::MOCKUP_POSSIBLE_VALIDITY)) {
            Yii::log("Invalid value for months of validity: $monthsOfValidity", "error");
            return null;
        }

        $validator = new yii\validators\EmailValidator();
        if (!$validator->validate($reviewerEmail, $error)) {
            Yii::log("Malformed value for reviewer's email $reviewerEmail: $error", "error");
            return null;
        }

        $api_endpoint = "http://fuw-admin-api/mockup-urls";

        // create a token for the mockup page
        $mockupToken =  (string) $tokenMaker->generateTokenForMockup(
            $reviewerEmail,
            $monthsOfValidity,
            $this->identifier
        );
        // reuse token to avoid "You must unsign before making changes" error
        // when multiple API calls in same session
        $this->token = $this->token ?? $this->tokenSrv->generateTokenForUser($this->requester->email);
        // create a new random unique url fragment
        $uuid = Uuid::uuid4();


        try {
            $response = $this->webClient->request('POST', $api_endpoint, [
                                    'headers' => [
                                        'Authorization' => "Bearer " . $this->token,
                                    ],
                                    'form_params' => [
                                        'jwt_token' => $mockupToken,
                                        'url_fragment' => $uuid->toString(),
                                    ],
                                    'connect_timeout' => 5,
                                ]);
            if (201 === $response->getStatusCode()) {
                //convert returned json to a PHP array so we can work with it
                $responseData = json_decode($response->getBody(), true);
                // add reviewer to fuw users so they can authenticate using JWT token when they annotate files
                $userData = $this->tokenSrv->createUser(
                    $this->token,
                    $this->webClient,
                    $reviewerEmail . "_" . $this->identifier,
                    $reviewerEmail
                );
                return [ $responseData['url_fragment'], $userData['id'] ] ;
            }
        } catch (RequestException $e) {
            Yii::log(Psr7\str($e->getRequest()), "error");
            if ($e->hasResponse()) {
                Yii::log(Psr7\str($e->getResponse()), "error");
            }
        }
        return null;
    }

    /**
     * Make HTTP POST to File Upload Wizard to trigger the job to move files
     *
     * @param int $filedrop_id internal id of filedrop account
     * @return ?array doi and list of jobs ids
     */
    public function triggerMoveFiles(int $filedrop_id): ?array
    {
        $api_endpoint = "http://fuw-admin-api/filedrop-accounts/move/$filedrop_id";

        // reuse token to avoid "You must unsign before making changes" error
        // when multiple API calls in same session
        $this->token = $this->token ?? $this->tokenSrv->generateTokenForUser($this->requester->email);

        try {
            $response = $this->webClient->request('POST', $api_endpoint, [
                                    'headers' => [
                                        'Authorization' => "Bearer " . $this->token,
                                    ],
                                    'connect_timeout' => 5,
                                ]);
            if (200 === $response->getStatusCode()) {
                // Yii::log($response->getBody(), "debug");
                return  json_decode($response->getBody(), true);
            }
        } catch (RequestException $e) {
            Yii::log(Psr7\str($e->getRequest()), "error");
            if ($e->hasResponse()) {
                Yii::log(Psr7\str($e->getResponse()), "error");
            }
        }
        return null;
    }
}
