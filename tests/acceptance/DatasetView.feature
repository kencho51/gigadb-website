Feature: a user visit the dataset page
  As a website user
  I want to see all the information pertaining to a dataset
  So that I can use it to further my research or education

  @ok
  Scenario: number of files in current page and total number of files are displayed
    Given I have not signed in
    When I am on "dataset/100142"
    Then I should see "Displaying 4 files of 4"

  @ok
  Scenario: pagination widget is not shown when total number of file is less or equal to page size setting
    Given I have not signed in
    When I am on "dataset/100142"
    Then I should not see "Go to page"
    And I should not see "of 1"

  @ok
  Scenario: pagination widget is shown when total number of file greater than the page size setting
    Given I have not signed in
    And I have set the page size setting to 5
    When I am on "/dataset/100006"
    And I follow "Files"
    Then I should see "Next >"
    Then I should see "Go to page"
    
  @ok @issue-877
  Scenario: The google scholar link is working
    When I am on "/dataset/100094"
    Then I should see an image "/images/google_scholar.png" is linked to "https://scholar.google.com/scholar?q=10.5072/100094"

  @ok @issue-877
  Scenario: The Euro PubMed Central link is working
    When I am on "/dataset/100094"
    Then I should see an image "/images/ePMC.jpg" is linked to "https://europepmc.org/search?scope=fulltext&query=(REF:%2710.5072/100094%27)"

  @ok @issue-877
  Scenario: The dimensions link is working
    When I am on "/dataset/100094"
    Then I should see an image "/images/dimensions.jpg" is linked to "https://app.dimensions.ai/discover/publication?search_text=10.5072/100094"


  @ok
  Scenario: Private dataset not visible
    Given I have not signed in
    When I am on "/dataset/200070"
    And I should not see "well now, how to describe nothing in particular?"
    Then I should see "The DOI 200070 cannot be displayed."

  @ok
  Scenario: Private dataset accessible through mockup url
    Given I have not signed in
    When I am on "/dataset/200070/token/ImP3Bbu7ytRSfYFh"
    Then I should see "well now, how to describe nothing in particular?"
    And I should not see "The DOI 200070 cannot be displayed."
