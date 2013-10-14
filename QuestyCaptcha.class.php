<?php /**
 * QuestyCaptcha class
 *
 * @file
 * @author Benjamin Lees <emufarmers@gmail.com>
 * @ingroup Extensions
 */ class QuestyCaptcha extends SimpleCaptcha {
	/** Validate a captcha response */
	function keyMatch( $answer, $info ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'questycaptcha_answer',
			array( 'qca_answer' ),
			'qca_id = ' . $info['id'],
			__METHOD__
		);
		foreach( $res as $row ) {
			if( strtolower( $answer ) === $row->qca_answer) {
				return true;
			}
		}
		return false;
	}
	function getCaptcha() {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->selectRow(
			'questycaptcha',
			array( 'qcq_question', 'qcq_id' ),
			'',
			__METHOD__,
					array( 'ORDER BY' => 'RAND()' )
		);

		if( empty( $res ) ) {
			die("No questions found. Add some via Special:CaptchaAdmin.");
		}

		return array( "question" => $res->qcq_question, "id" => $res->qcq_id );
	}
	function getForm() {
		$captcha = $this->getCaptcha();
		$index = $this->storeCaptcha( $captcha );
		return "<p><label for=\"wpCaptchaWord\">{$captcha['question']}</label> " .
			Html::element( 'input', array(
				'name' => 'wpCaptchaWord',
				'id' => 'wpCaptchaWord',
				'required',
				'tabindex' => 1 ) ) . // tab in before the edit textarea
			"</p>\n" .
			Xml::element( 'input', array(
				'type' => 'hidden',
				'name' => 'wpCaptchaId',
				'id' => 'wpCaptchaId',
				'value' => $index ) );
	}
	function getMessage( $action ) {
		$name = 'questycaptcha-' . $action;
		$text = wfMessage( $name )->text();
		# Obtain a more tailored message, if possible, otherwise, fall back to the default for edits
		return wfMessage( $name, $text )->isDisabled() ? wfMessage( 'questycaptcha-edit' )->text() : $text;
	}
	function showHelp() {
		global $wgOut;
		$wgOut->setPageTitle( wfMessage( 'captchahelp-title' )->text() );
		$wgOut->addWikiMsg( 'questycaptchahelp-text' );
		if ( CaptchaStore::get()->cookiesNeeded() ) {
			$wgOut->addWikiMsg( 'captchahelp-cookies-needed' );
		}
	}
	function passCaptcha() {
		$info = $this->retrieveCaptcha();
		if ( $info ) {
			global $wgRequest;
			if ( $this->keyMatch( $wgRequest->getVal( 'wpCaptchaWord' ), $info ) ) {
				$this->log( "passed" );
				$this->clearCaptcha( $info );
				$this->incrementPass( $info );
				return true;
			} else {
				$this->clearCaptcha( $info );
				$this->log( "bad form input" );
				$this->incrementFail( $info );
				return false;
			}
		} else {
			$this->log( "new captcha session" );
			return false;
		}
	}

	function incrementPass( $info ) {
		$this->increment($info, 'qcq_correct');  //change these to "right" and "wrong" or "pass" and "fail"
	}
	
	function incrementFail( $info ) {
		$this->increment($info, 'qcq_wrong');
	}
	
	function increment( $info, $column ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->selectRow(
			'questycaptcha',
			array( $column ),
			'qcq_id = ' . $info['id'],
			__METHOD__
		);
		$count = $res->$column;
		$dbw = wfGetDB( DB_MASTER );
		$dbw->update(
		 'questycaptcha',
		 array( $column => $count + 1 ),
		 array( 'qcq_id' => $info['id'] ),
		 __METHOD__
		 );
	}
}

