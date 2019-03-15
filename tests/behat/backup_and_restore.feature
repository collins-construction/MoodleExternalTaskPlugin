@qtype @qtype_externaltask
Feature: Test duplicating a quiz containing an Assay question
  As a teacher
  In order re-use my courses containing External task questions
  I need to be able to backup and restore them

  Background:
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype            | name             | template         |
      | Test questions   | externaltask     | externaltask-001 | editor           |
      | Test questions   | externaltask     | externaltask-002 | editorfilepicker |
      | Test questions   | externaltask     | externaltask-003 | plain            |
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Test quiz | C1     | quiz1    |
    And quiz "Test quiz" contains the following questions:
      | externaltask-001 | 1 |
      | externaltask-002 | 1 |
      | externaltask-003 | 1 |
    And I log in as "admin"
    And I am on "Course 1" course homepage

  @javascript
  Scenario: Backup and restore a course containing 3 External task questions
    When I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Course 2 |
    And I navigate to "Question bank" in current page administration
    And I should see "externaltask-001"
    And I should see "externaltask-002"
    And I should see "externaltask-003"
    And I click on "Edit" "link" in the "externaltask-001" "table_row"
    Then the following fields match these values:
      | Question name              | externaltask-001                                        |
      | Question text              | Please write a story about a frog.                      |
      | General feedback           | I hope your story had a beginning, a middle and an end. |
      | Response format            | HTML editor                                             |
      | Require text               | Require the student to enter text                       |
    And I press "Cancel"
    And I click on "Edit" "link" in the "externaltask-002" "table_row"
    Then the following fields match these values:
      | Question name              | externaltask-002                                        |
      | Question text              | Please write a story about a frog.                      |
      | General feedback           | I hope your story had a beginning, a middle and an end. |
      | Response format            | HTML editor with file picker                            |
      | Require text               | Require the student to enter text                       |
    And I press "Cancel"
    And I click on "Edit" "link" in the "externaltask-003" "table_row"
    Then the following fields match these values:
      | Question name              | externaltask-003                                        |
      | Question text              | Please write a story about a frog.                      |
      | General feedback           | I hope your story had a beginning, a middle and an end. |
      | Response format            | Plain text                                              |
      | Require text               | Require the student to enter text                       |
