@issue-375 @admin-project-update
Feature: a curator can update project attributes
  As a curator,
  I want to update project url, name and image location in admin project update page
  So that I can update project information

  Background:
    Given Gigadb web site is loaded with production-like data

  @ok @issue-375 @javascript
  Scenario: Log in as an admin and see all the attributes
    Given I sign in as an admin
    When I go to "/adminProject/update/id/2"
    Then I should see "Url"
    And I should see "Name"
    And I should see "Image Location"
    And I should see a button "Cancel"
    And I should see a button input "Save"

  @ok @issue-375 @javascript
  Scenario: Change Url and name
    Given I sign in as an admin
    When I go to "/adminProject/update/id/2"
    And I fill in "Url" with "http://www.google.com"
    And I fill in "Name" with "Test"
    And I press "Save"
    Then I should see "View Project #2"
    And I should see "http://www.google.com"
    And I should see "Test"

  @wip @issue-375 @javascript
  Scenario: Update project ID 2 Url only which should pass, but got Duplicate project name error
    Given I sign in as an admin
    When I go to "/adminProject/update/id/2"
    And I fill in "Url" with "http://www.genome10k.org/"
    And I press "Save"
    Then I should see "Duplicate Project Name"

  @wip @issue-375 @javascript
  Scenario: Update project ID 2 Url only which should not pass, because project ID 16 has the same project Url
    Given I sign in as an admin
    When I go to "/adminProject/update/id/2"
    And I fill in "Url" with "http://www.well.ox.ac.uk/converge"
    And I press "Save"
    Then I should see "Duplicate URL"

  @wip @issue-375 @javascript
  Scenario: Update project ID 2 Name only which should pass, but got Duplicate URL error
    Given I sign in as an admin
    When I go to "/adminProject/update/id/2"
    And I fill in "Name" with "Genome 10K"
    And I press "Save"
    Then I should see "Duplicate URL"

  @wip @issue-375 @javascript
  Scenario: Update project ID 2 Name only which should not pass, because project ID 9 has the same project Name
    Given I sign in as an admin
    When I go to "/adminProject/update/id/2"
    And I fill in "Name" with "Pear Genome Project"
    And I press "Save"
    Then I should see "Duplicate Project Name"

  @wip @issue-375 @javascript
  Scenario: Change image location which should pass, but got Duplicate project name error and Duplicate URL error
    Given I sign in as an admin
    When I go to "/adminProject/update/id/2"
    And I fill in "Image Location" with "http://gigadb.org/test.jpg"
    And I press "Save"
    Then I should see "Duplicate Project Name"
    And I should see "Duplicate URL"