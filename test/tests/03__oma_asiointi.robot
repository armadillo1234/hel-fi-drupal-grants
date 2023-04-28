*** Settings ***
Documentation       Robot test for testing oma asiointi
Metadata            Examplemetadata          This is a simple browser test for ${baseurl}. Metadata is shown in the reports.
Library             Browser
Library             String
Suite Setup
Resource            ../resources/common.resource
Resource            ../resources/tunnistamo.resource

*** Test Cases ***

Login And Check Oma Asiointi Data
    Open Browser To Home Page
    Accept Cookies Banner
    Do Company Login Process With Tunnistamo
    Go To Oma Asiointi
    Check Oma Asiointi Data
    Sort Sent Applications
    [Teardown]    Close Browser

*** Keywords ***

Check Oma Asiointi Data
    Get Element Count    h2\#keskeneraiset-hakemukset    ==    1
    Get Element Count    h2\#lahetetyt-hakemukset    ==    1
    # For this account, there should be multiple sent applications
    # Get Element Count    \#oma-asiointi__sent ul li     >=     1
    # Get Text    \#oma-asiointi__sent .application-list__count-value    !=     0

Sort Sent Applications
    Select Options By    \#applicationListSort    value    asc application-list__item--status
    Select Options By    \#applicationListSort    value    desc application-list__item--submitted
