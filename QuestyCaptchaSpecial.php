<?php
class SpecialQuestyCaptcha extends SpecialPage {
    public function __construct() {
        parent::__construct( 'QuestyCaptcha' );
    }

    /**
     * Show the special page
     *
     * @param $par Mixed: parameter passed to the page or null
     */
    public function execute( $par ) {
        $this->setHeaders();
        $this->outputHeader();

        $out = $this->getOutput();
        $request = $this->getRequest();

        $out->addModules( 'ext.interwiki.specialpage' );

        $action = $par ? $par : $request->getVal( 'action', $par );
        $return = $this->getTitle();

        switch( $action ) {
            case 'delete':
            case 'edit':
            case 'add':
                if ( $this->canModify( $out ) ) {
                    $this->showForm( $action );
                }
                $out->returnToMain( false, $return );
                break;
            case 'submit':
                if ( !$this->canModify( $out ) ) {
                    # Error msg added by canModify()
                } elseif ( !$request->wasPosted() || !$this->getUser()->matchEditToken( $request->getVal( 'wpEditToken' ) ) ) {
                    // Prevent cross-site request forgeries
                    $out->addWikiMsg( 'sessionfailure' );
                } else {
                    $this->doSubmit();
                }
                $out->returnToMain( false, $return );
                break;
            default:
                $this->showList();
                break;
        }
    }

    /**
     * Returns boolean whether the user can modify the data.
     * @param $out OutputPage|bool If $wgOut object given, it adds the respective error message.
     * @throws PermissionsError
     * @return bool
     */
    public function canModify( $out = false ) {
        global $wgInterwikiCache;
        if ( !$this->getUser()->isAllowed( 'interwiki' ) ) {
            # Check permissions
            if ( $out ) {
                throw new PermissionsError( 'interwiki' );
            }

            return false;
        } elseif ( $wgInterwikiCache ) {
            # Editing the interwiki cache is not supported
            if ( $out ) {
                $out->addWikiMsg( 'interwiki-cached' );
            }

            return false;
        } elseif ( wfReadOnly() ) {
            # Is the database in read-only mode?
            if ( $out ) {
                $out->readOnlyPage();
            }
            return false;
        }
        return true;
    }


    /**
     * @param $action string
     */
    function showForm( $action ) {
        $request = $this->getRequest();

        $qid = $request->getVal( 'prefix' );
        $wpPrefix = '';
        $label = array( 'class' => 'mw-label' );
        $input = array( 'class' => 'mw-input' );

        if ( $action === 'delete' ) {
            $topmessage = $this->msg( 'interwiki_delquestion', $qid )->text();
            $intromessage = $this->msg( 'interwiki_deleting', $qid )->escaped();
            $wpPrefix = Html::hidden( 'wpQuestycaptchaId', $qid );
            $button = 'delete';
            $formContent = '';
        } elseif ( $action === 'edit' ) {
            $dbr = wfGetDB( DB_SLAVE );
            $row = $dbr->selectRow( 'questycaptcha', '*', array( 'qcq_id' => $qid ), __METHOD__ );

            if ( !$row ) {
                $this->error( 'interwiki_editerror', $qid );
                return;
            }

            $row_answer = $dbr->selectRow( 'questycaptcha_answer', '*', array( 'qca_id' => $qid ), __METHOD__ );

            $id = $row->qcq_id;
            $defaulturl = $row->qcq_question;
            $answer = $row_answer->qca_answer;
            $transfixme = $row->qcq_correct;
            $localfixme = $row->qcq_wrong;
            $wpPrefix = Html::hidden( 'wpInterwikiPrefix', $row->qcq_id );
            $topmessage = $this->msg( 'questycaptcha_edittext' )->text();
            $intromessage = $this->msg( 'interwiki_editintro' )->escaped();
            $button = 'edit';
        } elseif ( $action === 'add' ) {
            $id = $request->getVal( 'wpInterwikiPrefix', $request->getVal( 'prefix' ) );
            $prefixElement = Xml::input( 'wpInterwikiPrefix', 20, $qid,
                array( 'tabindex' => 1, 'id' => 'mw-interwiki-prefix', 'maxlength' => 20 ) );
            $question = $request->getCheck( 'qcQuestion' );
            $trans = $request->getCheck( 'wpInterwikiTrans' );
            $defaulturl = $request->getVal( 'qcId', $this->msg( 'interwiki-defaulturl' )->text() );
            $topmessage = $this->msg( 'questycaptcha_addtext' )->text();
            $intromessage = $this->msg( 'interwiki_addintro' )->escaped();
            $button = 'interwiki_addbutton';
        }

        if ( $action === 'add' || $action === 'edit' ) {
            $formContent = Html::rawElement( 'tr', null,
                Html::element( 'td', $label, $this->msg( 'questycaptcha-qid-label' )->text() ) .
                    Html::rawElement( 'td', null, '<tt>' . $id . '</tt>' )
            ) . Html::rawElement( 'tr', null,
                Html::rawElement( 'td', $label, Xml::label( $this->msg( 'questycaptcha-question-label' )->text(), 'mw-interwiki-url' ) ) .
                    Html::rawElement( 'td', $input, Xml::input( 'qcQuestion', 60, $defaulturl,
                        array( 'tabindex' => 1, 'maxlength' => 200, 'id' => 'mw-interwiki-url' ) ) )
            ) . Html::rawElement( 'tr', null,
                    Html::rawElement( 'td', $label, Xml::label( $this->msg( 'questycaptcha-answer-label' )->text(), 'mw-interwiki-url' ) ) .
                    Html::rawElement( 'td', $input, Xml::input( 'qcAnswer', 60, $answer,
                        array( 'tabindex' => 1, 'maxlength' => 200, 'id' => 'mw-interwiki-url' ) ) )
                );
        }

        $form = Xml::fieldset( $topmessage, Html::rawElement( 'form',
            array( 'id' => "mw-interwiki-{$action}form", 'method' => 'post',
                'action' => $this->getTitle()->getLocalUrl( array( 'action' => 'submit', 'prefix' => $qid ) ) ),
            Html::rawElement( 'p', null, $intromessage ) .
                Html::rawElement( 'table', array( 'id' => "mw-interwiki-{$action}" ),
                    $formContent . Html::rawElement( 'tr', null,
                        Html::rawElement( 'td', $label, Xml::label( $this->msg( 'interwiki_reasonfield' )->text(),
                            "mw-interwiki-{$action}reason" ) ) .
                            Html::rawElement( 'td', $input, Xml::input( 'wpInterwikiReason', 60, '',
                                array( 'tabindex' => 1, 'id' => "mw-interwiki-{$action}reason", 'maxlength' => 200 ) ) )
                    ) .	Html::rawElement( 'tr', null,
                        Html::rawElement( 'td', null, '' ) .
                            Html::rawElement( 'td', array( 'class' => 'mw-submit' ),
                                Xml::submitButton( $this->msg( $button )->text(), array( 'id' => 'mw-interwiki-submit' ) ) )
                    ) . $wpPrefix .
                        Html::hidden( 'wpEditToken', $this->getUser()->getEditToken() ) .
                        Html::hidden( 'wpInterwikiAction', $action )
                )
        ) );
        $this->getOutput()->addHTML( $form );
        return;
    }

    function doSubmit() {
        global $wgContLang, $wgMemc;

        $request = $this->getRequest();
        $id = $request->getVal( 'qcId' );
        $do = $request->getVal( 'wpInterwikiAction' );
		/*
        // Show an error if the prefix is invalid (only when adding one).
        // Invalid characters for a title should also be invalid for a prefix.
        // Whitespace, ':', '&' and '=' are invalid, too.
        // (Bug 30599).
        global $wgLegalTitleChars;
        $validPrefixChars = preg_replace( '/[ :&=]/', '', $wgLegalTitleChars );
        if ( preg_match( "/\s|[^$validPrefixChars]/", $prefix ) && $do === 'add' ) {
            $this->error( 'interwiki-badprefix', htmlspecialchars( $prefix ) );
            $this->showForm( $do );
            return;
        }
		*/
        $prefix = $request->getVal( 'prefix' ); //hrm
        $reason = $request->getText( 'wpInterwikiReason' );
        $selfTitle = $this->getTitle();
        $dbw = wfGetDB( DB_MASTER );
        switch( $do ) {
            case 'delete':
				$id = $request->getVal( 'wpQuestycaptchaId' );
                $dbw->delete( 'questycaptcha', array( 'qcq_id' => $id ), __METHOD__ );

                if ( $dbw->affectedRows() === 0 ) {
                    $this->error( 'interwiki_delfailed', $id );
                    $this->showForm( $do );
                } else {
                    $this->getOutput()->addWikiMsg( 'interwiki_deleted', $id );
                    $log = new LogPage( 'interwiki' );
                    $log->addEntry( 'iw_delete', $selfTitle, $reason, array( $id ) );
                    $wgMemc->delete( wfMemcKey( 'interwiki', $id ) );
                }
                break;
            case 'add':
                $prefix = $wgContLang->lc( $prefix );
            // N.B.: no break!
            case 'edit':
                $question = $request->getVal( 'qcQuestion' );
            //    die($question);
                $answer = $request->getVal( 'qcAnswer' );
             //   die($answer);

                $data = array(
                    'qcq_id' => $prefix,
                    'qcq_question' => $question
                );

                $data_answer = array(
                    'qca_id' => $prefix,
                    'qca_answer' => $answer
                );

                if ( $id === '' || $question === '' ) {
                    $this->error( 'interwiki-submit-empty' );
                    $this->showForm( $do );
                    return;
                }

				/*
                // Simple URL validation: check that the protocol is one of
                // the supported protocols for this wiki.
                // (bug 30600)
                if ( !wfParseUrl( $theurl ) ) {
                    $this->error( 'interwiki-submit-invalidurl' );
                    $this->showForm( $do );
                    return;
                }
				*/

                //Example: $id = $dbw->nextSequenceValue( 'page_page_id_seq' ); $dbw->insert( 'page', array( 'page_id' => $id ) ); $id = $dbw->insertId();
                //FIXME USE THIS

                if ( $do === 'add' ) {
                    $dbw->insert( 'questycaptcha', $data, __METHOD__, 'IGNORE' );
                    $data_answer = array(
                        'qca_id' => $dbw->insertID(),
                        'qca_answer' => $answer
                    );
                    $dbw->insert( 'questycaptcha_answer', $data_answer, __METHOD__, 'IGNORE' );
                } else { // $do === 'edit'
                   // die($prefix);
                    $dbw->update( 'questycaptcha', $data, array( 'qcq_id' => $prefix ), __METHOD__, 'IGNORE' );  //FIXME ID and prefix are conflated
                    $dbw->update( 'questycaptcha_answer', $data_answer, array( 'qca_id' => $prefix ), __METHOD__, 'IGNORE' );
                }

                // used here: interwiki_addfailed, interwiki_added, interwiki_edited
                if ( $dbw->affectedRows() === 0 ) {
                    $this->error( "interwiki_{$do}failed", $prefix );
                    $this->showForm( $do );
                } else {
                    $this->getOutput()->addWikiMsg( "interwiki_{$do}ed", $prefix );
                    $log = new LogPage( 'interwiki' );
                    $theurl = $trans = $local = ''; //FIXME
                    $log->addEntry( 'iw_' . $do, $selfTitle, $reason, array( $prefix, $theurl, $trans, $local ) );
                    $wgMemc->delete( wfMemcKey( 'interwiki', $prefix ) );
                }
                break;
        }
    }

    function showList() {
        $canModify = $this->canModify();

        $this->getOutput()->addWikiMsg( 'questycaptcha_intro' );
        // Make collapsible.
        $this->getOutput()->addHTML(
            Html::openElement(
                'div', array(
                'class' => 'mw-collapsible mw-collapsed',
                'data-collapsetext' => $this->msg( 'interwiki-legend-hide' )->escaped(),
                'data-expandtext' => $this->msg('interwiki-legend-show' )->escaped()
            ) ) );
        $this->getOutput()->addHTML(
            Html::rawElement(
                'table', array( 'class' => 'mw-interwikitable wikitable intro' ),
                $this->addInfoRow( 'start', 'questycaptcha_id', 'questycaptcha_id_intro' ) . "\n" .
                    $this->addInfoRow( 'start', 'questycaptcha_question', 'questycaptcha_question_intro' ) . "\n" .
                    $this->addInfoRow( 'start', 'questycaptcha_correct', 'questycaptcha_correct_intro' ) . "\n" .
                    $this->addInfoRow( 'start', 'questycaptcha_wrong', 'questycaptcha_wrong_intro' ) . "\n" .
					$this->addInfoRow( 'start', 'questycaptcha_answers', 'questycaptcha_answers_intro' ) . "\n"
            )
        );

        $this->getOutput()->addHTML( Html::closeElement( 'table' ) );
        $this->getOutput()->addHTML( Html::closeElement( 'div' ) ); // end collapsible.

        if ( $canModify ) {
            $this->getOutput()->addHTML( "<br />" . $this->msg( 'interwiki_intro_footer' )->parse() );
            $addtext = $this->msg( 'questycaptcha_addtext' )->escaped();
            $addlink = Linker::linkKnown( $this->getTitle( 'add' ), $addtext );
            $this->getOutput()->addHTML( '<p class="mw-interwiki-addlink">' . $addlink . '</p>' );
        }

        if ( !method_exists( 'Interwiki', 'getAllPrefixes' ) ) {
            # version 2.0 is not backwards compatible (but still display nice error)
            $this->error( 'interwiki_error' );
            return;
        }
        $questions = $this->getQuestions( null );

        if ( !is_array( $questions ) || count( $questions ) === 0 ) {
            # If the interwiki table is empty, display an error message
            $this->error( 'questycaptcha_noquestions' );
            return;
        }

        $out = '';

        # Output the existing Interwiki prefixes table header
        $out .=	Html::openElement( 'table', array( 'class' => 'mw-interwikitable wikitable sortable body' ) ) . "\n";
        $out .= Html::openElement( 'tr', array( 'id' => 'interwikitable-header' ) ) .
            Html::element( 'th', null, $this->msg( 'questycaptcha_id' )->text() ) .
            Html::element( 'th', null, $this->msg( 'questycaptcha_question' )->text() ) .
            Html::element( 'th', null, $this->msg( 'questycaptcha_correct' )->text() ) .
            Html::element( 'th', null, $this->msg( 'questycaptcha_wrong' )->text() ) .
			Html::element( 'th', null, $this->msg( 'questycaptcha_answers' )->text() ) .
            ( $canModify ? Html::element( 'th', array( 'class' => 'unsortable' ), $this->msg( 'questycaptcha_edit' )->text() ) : '' );
        $out .= Html::closeElement( 'tr' ) . "\n";

        $selfTitle = $this->getTitle();

        $answers = $this->getAnswers( null );

        # Output the existing Interwiki prefixes table rows
        foreach ( $questions as $question ) {
            $out .= Html::openElement( 'tr', array( 'class' => 'mw-interwikitable-row' ) );
            $out .= Html::element( 'td', array( 'class' => 'mw-interwikitable-prefix' ),
                $question['qcq_id'] );
            $out .= Html::element( 'td', array( 'class' => 'mw-interwikitable-url' ), $question['qcq_question'] );
            $attribs = array( 'class' => 'mw-interwikitable-local' );
            // The messages interwiki_0 and interwiki_1 are used here.
            $out .= Html::element( 'td', $attribs, $question['qcq_correct'] );
            $attribs = array( 'class' => 'mw-interwikitable-trans' );

            // The messages interwiki_0 and interwiki_1 are used here.
            $out .= Html::element( 'td', $attribs, $question['qcq_wrong'] );

			$out .= '<td class="mw-interwikitable-trans">';
            foreach ( $answers as $answer ) {
                if( $answer['qca_id'] === $question['qcq_id'] ) {
                    $out .= $answer['qca_answer'] . '<br/>';
                }
            }
			$out .= '</td>';

            // Additional column when the interwiki table can be modified.
            if ( $canModify ) {
                $out .= Html::rawElement( 'td', array( 'class' => 'mw-interwikitable-modify' ),
                    Linker::linkKnown( $selfTitle, $this->msg( 'edit' )->escaped(), array(),
                        array( 'action' => 'edit', 'prefix' => $question['qcq_id'] ) ) .
                        $this->msg( 'comma-separator' ) .
                        Linker::linkKnown( $selfTitle, $this->msg( 'delete' )->escaped(), array(),
                            array( 'action' => 'delete', 'prefix' => $question['qcq_id'] ) )
                );
            }
            $out .= Html::closeElement( 'tr' ) . "\n";
        }
        $out .= Html::closeElement( 'table' );

        $this->getOutput()->addHTML( $out );
    }

    /**
     * Adds a row to the documentation table on the top of Special:Interwiki.
     * @param $align string
     * @param $title string
     * @param $text string
     * @return string
     */
    private function addInfoRow( $align = 'start', $title, $text ) {
        return Html::rawElement( 'tr', null,
            // The classes mw-align-start and mw-align-end are used here.
            Html::rawElement( 'th', array( 'class' => 'mw-align-' . $align ), $this->msg( $title )->escaped() ) .
                // This message is expected to have wiki syntax
                Html::rawElement( 'td', null, $this->msg( $text )->parse() )
        );
    }

    function error() {
        $args = func_get_args();
        $this->getOutput()->wrapWikiMsg( "<p class='error'>$1</p>", $args );
    }

    function getQuestions() {
        $db = wfGetDB( DB_SLAVE );

        $where = array();

        $res = $db->select( 'questycaptcha',
            self::selectFields(),
            $where, __METHOD__, array( 'ORDER BY' => 'qcq_id' )
        );
        $retval = array();
        foreach ( $res as $row ) {
            $retval[] = (array)$row;
        }
        return $retval;

    }

    function getAnswers() {
        $db = wfGetDB( DB_SLAVE );

        $where = array();

        $res = $db->select( 'questycaptcha_answer',
            self::selectFieldsAnswers(),
            $where, __METHOD__, array( 'ORDER BY' => 'qca_id' )
        );
        $retval = array();
        foreach ( $res as $row ) {
            $retval[] = (array)$row;
        }
        return $retval;

    }

    function selectFields() {
        return array(
            'qcq_question',
            'qcq_id',
            'qcq_correct',
            'qcq_wrong'
        );
    }

    function selectFieldsAnswers() {
        return array(
            'qca_id',
            'qca_answer',
        );
    }
}
