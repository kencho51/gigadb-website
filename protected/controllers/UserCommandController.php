<?php

use yii\swiftmailer\mailer;
use yii\swiftmailer\Message;

class UserCommandController extends CController
{
    // Members



	/**
	 * @return array action filters
	 */

        public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(

			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('claim','cancelClaim'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
     * Record an authorship claim by a user on the dataset.
     * If claim is recorded successfully, the browser will be redirected to the 'view' page.
     * @param integer $dataset_id, dataset id
     * @param integer $author_id, dataset id
     */
    public function actionClaim($dataset_id, $author_id) {

        Yii::log(__FUNCTION__." ($dataset_id, $author_id)",'info');

    	$result['status'] = false;
        $result['message'] = "there was an error";

        $dataset_author = DatasetAuthor::model()->findByAttributes(array('dataset_id' => $dataset_id,
                                                                        'author_id' => $author_id));

        $user = User::model()->findByPk(Yii::app()->user->id);

        if( null != $dataset_author ){
            $requester_id = Yii::app()->user->id;
            $actionable_id = $dataset_author->author->id ;
        }
        else {
            $result['message'] = "mismatch between author and dataset.";
            echo json_encode($result);
            Yii::app()->end();
        }

        $existing_record = UserCommand::model()->findByAttributes(array('requester_id'  => $requester_id,
                                                                'actionable_id' => $actionable_id));

        if ( null != $existing_record) {
            if  ("rejected" == $existing_record->status) {
                $result['message'] = "We cannot submit the claim: Your claim on this author has already been rejected.";
                echo json_encode($result);
                Yii::app()->end();
            }
            else if  ("linked" == $existing_record->status) {
                $result['message'] = "We cannot submit the claim: Your claim on this author has already been approved.";
                echo json_encode($result);
                Yii::app()->end();
            }
            else if  ("pending" == $existing_record->status) {
                $result['message'] = "We cannot submit the claim: You already have a pending claim.";
                echo json_encode($result);
                Yii::app()->end();
            }
        }

        $action_label = "claim_author";
        $status = "pending";
        $now = new Datetime();

        $claim = new UserCommand;
        $claim->action_label = $action_label;
        $claim->requester_id = $requester_id;
        $claim->actionable_id = $actionable_id;
        $claim->request_date = $now->format(DateTime::ISO8601) ;
        $claim->status = $status ;

		if ($claim->validate('insert')) {

            if ($claim->save(false)) {
                $this->sendNotificationEmail($user,$dataset_author->author,$dataset_author->dataset);
                $result['status'] = true;
                $result['message'] = "Your claim has been submitted to the administrators.";
                Yii::log(__FUNCTION__."> created user_command successfully for: ". $claim->requester->id, 'info');
            }
            else {
                Yii::log(__FUNCTION__."> create user_command failed", 'error');
                $result['message'] = "We cannot submit the claim: database error";
            }
        }
        else {
            Yii::log(__FUNCTION__."> validation of user_command failed:", 'error');
            $errors = $claim->getErrors();
            if ( isset($errors["requester_id"]) ) {
                $result['message'] = "We cannot submit the claim: validation error on unique keys constraint";

            }
            else {
                $result['message'] = "We cannot submit the claim: validation error";
            }
            $result['status'] = false;
        }


        echo json_encode($result);
		Yii::app()->end();

    }

    /**
     * Record an authorship claim by a user on the dataset.
     * If claim is recorded successfully, the browser will be redirected to the 'view' page.
     * @param integer $dataset_id, dataset id
     * @param integer $author_id, dataset id
     */
    public function actionCancelClaim() {

    	$result['status'] = false;
        $claim = UserCommand::model()->findByAttributes( array('requester_id' => Yii::app()->user->id) );

        if( null != $claim ){
	        Yii::log(__FUNCTION__."> deleting record {$claim->id} in user_command ", 'warning');
        	$claim->delete();
        	$result['status'] = true;
            $result['message'] = "Your claim has been successfully canceled.";
        }
        else {
            $result['status'] = false;
            $result['message'] = "You haven't got any current claim.";
        }

        echo json_encode($result);
		Yii::app()->end();

    }

    /**
     * Send notification email to admins about new submitted dataset.
     *
     * @param integer $user, user id
     * @param integer $dataset_id, dataset id
     * @param integer $author_id, author id
     */
    private function sendNotificationEmail($user, $author, $dataset) {
        $recipient = Yii::app()->params['notify_email'];
        $subject = Yii::app()->params['email_prefix'] . "New claim on a dataset author";
        $dataset_url = $this->createAbsoluteUrl('dataset/view',array('id'=>$dataset->identifier));
        $cta_url = $this->createAbsoluteUrl('user/update', array('id'=>$user->id));
        $body = <<<EO_MAIL
<p>New claim on an author made by user from this dataset:</p>
<p>$dataset_url</p>
<p>Claimant: {$user->first_name} {$user->last_name}</p>
<p>Author claimed:  {$author->getFullAuthor()}</p>
<p>Click the following url to validate or reject the claim:</p>
<p>$cta_url</p>
EO_MAIL;
        Yii::app()->mailService->sendHTMLEmail($recipient, $recipient, $subject, $body);
        Yii::log(__FUNCTION__."> Sent email to $recipient, about: $subject");
    }
}

?>
