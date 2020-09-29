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

  @wip @issue-375 @javascript
  Scenario: Change Url
    Given I sign in as an admin
    When I go to "/adminProject/update/id/2"
    And I fill in "Url" with "www.google.com"
    And I fill in "Name" with "Test"
    And I press "Save"
    Then I should see "View Project #2"
