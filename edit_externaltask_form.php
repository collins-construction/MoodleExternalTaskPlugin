<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines the editing form for the external task question type.
 *
 * @package    qtype
 * @subpackage externaltask
 * @copyright  2019 Collins Construction
 * @copyright  based on work by 2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * External task question type editing form.
 *
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_externaltask_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        $qtype = question_bank::get_qtype('externaltask');

        $mform->addElement('header', 'responseoptions', get_string('responseoptions', 'qtype_externaltask'));
        $mform->setExpanded('responseoptions');

        $mform->addElement('text', 'taskcomplete', get_string('taskcomplete', 'qtype_externaltask'), 'maxlength="100"  size="50"');
        $mform->setType('taskcomplete', PARAM_RAW);
        $mform->addRule('taskcomplete', null, 'required', null, 'client');
        $mform->setDefault('taskcomplete', 'I have completed the task');

        $mform->addElement('select', 'responseformat',
                '', $qtype->response_formats(), ['hidden']);
        $mform->setDefault('responseformat', 'editor');

        $mform->addElement('select', 'responserequired',
                '', $qtype->response_required_options(), ['hidden']);
        $mform->setDefault('responserequired', 1);
        $mform->disabledIf('responserequired', 'responseformat', 'eq', 'noinline');

        $mform->addElement('select', 'responsefieldlines',
                '', $qtype->response_sizes(), ['hidden']);
        $mform->setDefault('responsefieldlines', 15);
        $mform->disabledIf('responsefieldlines', 'responseformat', 'eq', 'noinline');

        $mform->addElement('select', 'attachments',
                '', $qtype->attachment_options(), ['hidden']);
        $mform->setDefault('attachments', 0);

        $mform->addElement('select', 'attachmentsrequired',
                '', $qtype->attachments_required_options(), ['hidden']);
        $mform->setDefault('attachmentsrequired', 0);
        $mform->disabledIf('attachmentsrequired', 'attachments', 'eq', 0);

        $mform->addElement('hidden', 'filetypeslist', '');
        $mform->setType('filetypeslist', PARAM_TEXT);

        $mform->addElement('hidden', 'responsetemplate[text]', '');
        $mform->setType('responsetemplate[text]', PARAM_TEXT);
        $mform->addElement('hidden', 'responsetemplate[format]', 1);
        $mform->setType('responsetemplate[format]', PARAM_INT);

        $mform->addElement('header', 'graderinfoheader', get_string('graderinfoheader', 'qtype_externaltask'));
        $mform->setExpanded('graderinfoheader');
        $mform->addElement('editor', 'graderinfo', get_string('graderinfo', 'qtype_externaltask'),
                array('rows' => 10), $this->editoroptions);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        if (empty($question->options)) {
            return $question;
        }

        $question->taskcomplete = $question->options->taskcomplete;
        $question->responseformat = $question->options->responseformat;
        $question->responserequired = $question->options->responserequired;
        $question->responsefieldlines = $question->options->responsefieldlines;
        $question->attachments = $question->options->attachments;
        $question->attachmentsrequired = $question->options->attachmentsrequired;
        $question->filetypeslist = $question->options->filetypeslist;

        $draftid = file_get_submitted_draft_itemid('graderinfo');
        $question->graderinfo = array();
        $question->graderinfo['text'] = file_prepare_draft_area(
            $draftid,           // Draftid
            $this->context->id, // context
            'qtype_externaltask',      // component
            'graderinfo',       // filarea
            !empty($question->id) ? (int) $question->id : null, // itemid
            $this->fileoptions, // options
            $question->options->graderinfo // text.
        );
        $question->graderinfo['format'] = $question->options->graderinfoformat;
        $question->graderinfo['itemid'] = $draftid;

        $question->responsetemplate = array(
            'text' => $question->options->responsetemplate,
            'format' => $question->options->responsetemplateformat,
        );

        return $question;
    }

    public function validation($fromform, $files) {
        $errors = parent::validation($fromform, $files);

        // Don't allow both 'no inline response' and 'no attachments' to be selected,
        // as these options would result in there being no input requested from the user.
        if ($fromform['responseformat'] == 'noinline' && !$fromform['attachments']) {
            $errors['attachments'] = get_string('mustattach', 'qtype_externaltask');
        }

        // If 'no inline response' is set, force the teacher to require attachments;
        // otherwise there will be nothing to grade.
        if ($fromform['responseformat'] == 'noinline' && !$fromform['attachmentsrequired']) {
            $errors['attachmentsrequired'] = get_string('mustrequire', 'qtype_externaltask');
        }

        // Don't allow the teacher to require more attachments than they allow; as this would
        // create a condition that it's impossible for the student to meet.
        if ($fromform['attachments'] != -1 && $fromform['attachments'] < $fromform['attachmentsrequired'] ) {
            $errors['attachmentsrequired']  = get_string('mustrequirefewer', 'qtype_externaltask');
        }

        return $errors;
    }

    public function qtype() {
        return 'externaltask';
    }
}
