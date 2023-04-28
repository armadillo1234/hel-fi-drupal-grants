*** Settings ***
Documentation       Robot test for testing application form and handling
Metadata            Examplemetadata          This is a simple browser test for ${baseurl}. Metadata is shown in the reports.
Library             Browser
Library             String
Library             DateTime
Suite Setup
Resource            ../resources/common.resource
Resource            ../resources/tunnistamo.resource
Resource            ../resources/dev-env-variables.resource

*** Test Cases ***

Fill yleisavustushakemus Form
    Open Browser To Home Page
    Accept Cookies Banner
    Do Company Login Process With Tunnistamo
    Go To Application Search
    Start New Application
    Fill Step 1 Data
    Fill Step 2 Data
    Save Application As Draft
    Fill Step 3 Data
    Fill Step 4 Data
    Review Application Data
    Completion Page
    [Teardown]    Close Browser

*** Keywords ***

Go To Application Search
    Click          \#block-mainnavigation a[data-drupal-link-system-path="etsi-avustusta"]
    Get Title           ==    Etsi avustusta | ${SITE_NAME}

Start New Application
    Click      .view-application-search \#kaupunginhallitus--yleisavustus
    Click      \#block-servicepageauthblock .hds-button
    Get Title           ==    Kaupunginhallituksen yleisavustushakemus | ${SITE_NAME}
    Wait For Elements State       li[data-webform-page="1_hakijan_tiedot"].is-active  visible

Fill Step 1 Data
    Scroll To Element     \#edit-email
    Type Text          \#edit-email     ${INPUT_EMAIL}
    Type Text          \#edit-contact-person     ${INPUT_CONTACT_PERSON}
    Type Text          \#edit-contact-person-phone-number     ${INPUT_CONTACT_PERSON_PHONE_NUMBER}
    Select Options By     \#edit-community-address-community-address-select   index     ${INPUT_COMMUNITY_ADDRESS_INDEX}
    Click       \#edit-actions-wizard-next
    Wait For Elements State       \#edit-bank-account-account-number-select       focused
    Select Options By       \#edit-bank-account-account-number-select    value    ${INPUT_BANK_ACCOUNT_NUMBER}
    Click       \#edit-actions-wizard-next
    Wait For Elements State      li[data-webform-page="2_avustustiedot"].is-active   visible

Fill Step 2 Data
    Scroll To Element     \#edit-acting-year
    ${today} = 	          Get Current Date     result_format=datetime
    ${current_year} =       Convert To String    ${today.year}
    Select Options By     \#edit-acting-year   value            ${current_year}
    Type Text             \#edit-subventions-items-0-amount     ${INPUT_SUBVENTION_AMOUNT}
    Sleep   1    # Have to manually wait for js formatter
    Get Text              \#edit-subventions-items-0-amount    ==     ${INPUT_SUBVENTION_AMOUNT_FORMATTED}
    Type Text             \#edit-compensation-purpose           ${INPUT_COMPENSATION_PURPOSE}
    Get Text              \#edit-compensation-purpose ~ .text-count-wrapper .text-count     !=    5000
    Scroll To Element     \#edit-olemme-saaneet-muita-avustuksia-ei
    Get Attribute         \#edit-olemme-saaneet-muita-avustuksia-ei    checked     ==    checked
    Scroll To Element     \#edit-compensation-boolean-false ~ label
    Get Attribute         \#edit-compensation-boolean-false     checked     !=    checked
    Get Attribute         \#edit-compensation-boolean-true    checked     !=    checked
    Click                 \#edit-compensation-boolean-true ~ label
    Wait For Elements State    \#edit-compensation-explanation    visible
    Type Text             \#edit-compensation-explanation         ${INPUT_COMPENSATION_EXPLANATION}
    Click       \#edit-actions-wizard-next
    Wait For Elements State      li[data-webform-page="3_yhteison_tiedot"].is-active   visible

Save Application as Draft
    Wait Until Network Is Idle
    Click                \#edit-actions-draft     # Can sometimes take up to 60s
    Wait For Elements State    .webform-submission__application_id .webform-submission__application_id--body    visible
    Scroll To Element    .webform-submission__application_id .webform-submission__application_id--body
    ${application_id} =   Get Text    .webform-submission__application_id .webform-submission__application_id--body
    Get Url   *=    ${application_id}     # Application id should be in the url
    # Go back to editing application
    Click                a[data-drupal-selector="application-edit-link"]
    Wait For Elements State      li[data-webform-page="3_yhteison_tiedot"].is-active   visible

Fill Step 3 Data
    Scroll To Element     \#edit-business-info
    Get Text              \#edit-community-purpose--description     !=    ${EMPTY}
    Get Text              \#edit-community-practices-business-0 ~ label    ==    Ei
    Click                 \#edit-community-practices-business-0 ~ label
    Scroll To Element     \#edit-fee-person
    Type Text             \#edit-fee-person     ${INPUT_FEE_PERSON}
    Sleep   1    # Have to manually wait for js formatter
    Get Text              \#edit-fee-person    ==     ${INPUT_FEE_PERSON_FORMATTED}
    Scroll To Element     \#edit-jasenmaara
    Hover                 \#edit-jasenmaara .webform-element-help-container--title:first-of-type .webform-element-help
    Wait For Elements State    \#tippy-2    visible
    Wait For Elements State    \#edit-jasenmaara .webform-element-help-container--title:first-of-type .webform-element-help[aria-expanded="true"]   visible
    Click       \#edit-actions-wizard-next
    Wait For Elements State      li[data-webform-page="lisatiedot_ja_liitteet"].is-active   visible

Fill Step 4 Data
    Scroll To Element     \#edit-yhteison-saannot-attachment
    Upload File By Selector    \#edit-yhteison-saannot-attachment-upload    ${CURDIR}/empty.pdf
    Sleep   3   # Have to manually wait for ajax upload
    Scroll To Element     \#edit-vahvistettu-tilinpaatos-attachment
    Upload File By Selector    \#edit-vahvistettu-tilinpaatos-attachment-upload    ${CURDIR}/empty.pdf
    Sleep   3   # Have to manually wait for ajax upload
    Scroll To Element     \#edit-vahvistettu-toimintakertomus-attachment
    Upload File By Selector    \#edit-vahvistettu-toimintakertomus-attachment-upload    ${CURDIR}/empty.pdf
    Sleep   3   # Have to manually wait for ajax upload
    Scroll To Element     \#edit-vahvistettu-tilin-tai-toiminnantarkastuskertomus-attachment
    Upload File By Selector    \#edit-vahvistettu-tilin-tai-toiminnantarkastuskertomus-attachment-upload    ${CURDIR}/empty.pdf
    Sleep   3   # Have to manually wait for ajax upload
    Scroll To Element     \#edit-vuosikokouksen-poytakirja-attachment
    Upload File By Selector    \#edit-vuosikokouksen-poytakirja-attachment-upload    ${CURDIR}/empty.pdf
    Sleep   3   # Have to manually wait for ajax upload
    Scroll To Element     \#edit-toimintasuunnitelma-attachment
    Upload File By Selector    \#edit-toimintasuunnitelma-attachment-upload    ${CURDIR}/empty.pdf
    Sleep   3   # Have to manually wait for ajax upload
    Scroll To Element     \#edit-talousarvio-attachment
    Upload File By Selector    \#edit-talousarvio-attachment-upload    ${CURDIR}/empty.pdf
    Sleep   3   # Have to manually wait for ajax upload
    Click       \#edit-actions-preview-next
    Wait For Elements State      li[data-webform-page="webform_preview"].is-active   visible

Review Application Data
    Get Text    \#yleisavustushakemus--contact_person   *=    ${INPUT_CONTACT_PERSON}
    Get Text    \#yleisavustushakemus--contact_person_phone_number   *=    ${INPUT_CONTACT_PERSON_PHONE_NUMBER}
    Get Text    \#yleisavustushakemus--bank_account   *=    ${INPUT_BANK_ACCOUNT_NUMBER}
    Get Text    \#yleisavustushakemus--subventions   *=    ${INPUT_SUBVENTION_AMOUNT_FORMATTED}
    Get Text    \#yleisavustushakemus--compensation_purpose   *=    ${INPUT_COMPENSATION_PURPOSE}
    Get Text    \#yleisavustushakemus--compensation_explanation   *=    ${INPUT_COMPENSATION_EXPLANATION}
    Get Text    \#yleisavustushakemus--fee_person   *=    ${INPUT_FEE_PERSON_FORMATTED}
    Wait Until Network Is Idle
    Click       \#accept_terms_1 ~ label
    Click       \#edit-actions-submit

Completion Page
    Get Text    \#avustushakemus-lahetetty-onnistuneesti    ==    Avustushakemus lähetetty onnistuneesti
    ${application_id} =   Get Text    .breadcrumb a:last-of-type
    Get Url   *=    ${application_id}     # Application id should be in the url
