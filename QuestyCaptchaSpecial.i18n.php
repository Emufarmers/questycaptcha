<?php
/**
 * Internationalisation file for Interwiki extension.
 *
 * @file
 * @ingroup Extensions
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * @author Stephanie Amanda Stevens <phroziac@gmail.com>
 * @author SPQRobin <robin_1273@hotmail.com>
 * @copyright Copyright (C) 2005-2007 Stephanie Amanda Stevens
 * @copyright Copyright (C) 2007 SPQRobin
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$messages = array();

/** English (English)
 * @author Stephanie Amanda Stevens
 * @author SPQRobin
 * @author Purodha
 */
$messages['en'] = array(
	# general messages
    'questycaptcha' => 'View and edit CAPTCHA questions',
	'interwiki' => 'View and edit interwiki data',
	'interwiki-title-norights' => 'View interwiki data',
	'interwiki-desc' => 'Adds a [[Special:Interwiki|special page]] to view and edit the interwiki table',
	'questycaptcha_intro' => 'This is a list of all CAPTCHA questions.',
	'interwiki-legend-show' => 'Show legend',
	'interwiki-legend-hide' => 'Hide legend',
	'questycaptcha_id' => 'ID',
	'questycaptcha-qid-label' => 'Question ID:',
	'questycaptcha_id_intro' => 'The permanent numeric ID of the question.',
	'questycaptcha_question' => 'Question',
	'questycaptcha-question-label' => 'Question:',
    'questycaptcha-answer-label' => 'Answer:',
	'questycaptcha_question_intro' => 'The question.',
	'questycaptcha_correct' => 'Correct',
	'questycaptcha_correct_intro' => 'The number of times the question has been answered correctly',
	'questycaptcha_wrong' => 'Wrong',
	'questycaptcha_wrong_intro' => 'The number of times the question has been answered incorrectly',
	'interwiki_intro_footer' => 'See [//www.mediawiki.org/wiki/Manual:Interwiki_table MediaWiki.org] for more information about the interwiki table.
There is a [[Special:Log/interwiki|log of changes]] to the interwiki table.',
	'interwiki_error' => 'Error: The interwiki table is empty, or something else went wrong.', //this will just be if there are no questions yet
    'questycaptcha_noquestions' => 'No CAPTCHA questions have been defined yet.',
	'interwiki-cached' => 'The interwiki data is cached. Modifying the cache is not possible.',

	
	'questycaptcha_answers' => 'Answers',
	'questycaptcha_answers_intro' => 'The answers to the question. Answers are not case-sensitive, and each question can have any number of answers.',

	
	
	# modifying permitted
	'questycaptcha_edit' => 'Edit',
	'interwiki_reasonfield' => 'Reason:',

	# deleting a prefix
	'interwiki_delquestion' => 'Deleting "$1"',
	'interwiki_deleting' => 'You are deleting prefix "$1".',
	'interwiki_deleted' => 'Prefix "$1" was successfully removed from the interwiki table.',
	'interwiki_delfailed' => 'Prefix "$1" could not be removed from the interwiki table.',

	# adding a prefix
	'questycaptcha_addtext' => 'Add a question',
	'interwiki_addintro' => 'You are adding a new interwiki prefix.
Remember that it cannot contain spaces ( ), colons (:), ampersands (&), or equal signs (=).',
	'interwiki_addbutton' => 'Add',
	'interwiki_added' => 'Prefix "$1" was successfully added to the interwiki table.',
	'interwiki_addfailed' => 'Prefix "$1" could not be added to the interwiki table.
Possibly it already exists in the interwiki table.',
	'interwiki-defaulturl' => 'http://www.example.com/$1', # do not translate or duplicate this message to other languages

	# editing a prefix
	'questycaptcha_edittext' => 'Editing a CAPTCHA question',
	'interwiki_editintro' => 'You are editing an interwiki prefix.
Remember that this can break existing links.',
	'interwiki_edited' => 'Prefix "$1" was successfully modified in the interwiki table.',
	'interwiki_editerror' => 'Prefix "$1" could not be modified in the interwiki table.
Possibly it does not exist.',
	'interwiki-badprefix' => 'Specified interwiki prefix "$1" contains invalid characters',
	'interwiki-submit-empty' => 'The prefix and URL cannot be empty.',
	'interwiki-submit-invalidurl' => 'The protocol of the URL is invalid.',

	# interwiki log
	'log-name-interwiki' => 'Interwiki table log',
	'logentry-interwiki-iw_add' => '$1 {{GENDER:$2|added}} prefix "$4" ($5) (trans: $6; local: $7) to the interwiki table',
	'logentry-interwiki-iw_edit' => '$1 {{GENDER:$2|modified}} prefix "$4" ($5) (trans: $6; local: $7) in the interwiki table',
	'logentry-interwiki-iw_delete' => '$1 {{GENDER:$2|removed}} prefix "$4" from the interwiki table',
	'log-description-interwiki' => 'This is a log of changes to the [[Special:Interwiki|interwiki table]].',
	'logentry-interwiki-interwiki' => '', # do not translate this message

	# rights
	'right-interwiki' => 'Edit interwiki data',
	'action-interwiki' => 'change this interwiki entry',
);

/** Message documentation (Message documentation)
 * @author Amire80
 * @author Fryed-peach
 * @author Jon Harald Søby
 * @author Meno25
 * @author Mormegil
 * @author Nemo bis
 * @author Purodha
 * @author Raymond
 * @author SPQRobin
 * @author Shirayuki
 * @author Siebrand
 * @author Umherirrender
 */
$messages['qqq'] = array(
//todo
);