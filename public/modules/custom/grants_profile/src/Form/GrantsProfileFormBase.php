<?php

namespace Drupal\grants_profile\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\grants_profile\GrantsProfileService;
use PHP_IBAN\IBAN;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Grants Profile form base.
 */
abstract class GrantsProfileFormBase extends FormBase {

  use StringTranslationTrait;

  /**
   * Drupal\Core\TypedData\TypedDataManager definition.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected TypedDataManager $typedDataManager;

  /**
   * Access to grants profile services.
   *
   * @var \Drupal\grants_profile\GrantsProfileService
   */
  protected GrantsProfileService $grantsProfileService;

  /**
   * Constructs a new GrantsProfileForm object.
   *
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   Data manager.
   * @param \Drupal\grants_profile\GrantsProfileService $grantsProfileService
   *   Profile.
   */
  public function __construct(TypedDataManager $typed_data_manager, GrantsProfileService $grantsProfileService) {
    $this->typedDataManager = $typed_data_manager;
    $this->grantsProfileService = $grantsProfileService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): GrantsProfileFormBase|static {
    return new static(
      $container->get('typed_data_manager'),
      $container->get('grants_profile.service')
    );
  }

  /**
   * Helper method so we can have consistent dialog options.
   *
   * @return string[]
   *   An array of jQuery UI elements to pass on to our dialog form.
   */
  public static function getDataDialogOptions(): array {
    return [
      'width' => '33%',
    ];
  }

  /**
   * Ajax callback.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Forms state.
   *
   * @return mixed
   *   Form element for replacing.
   */
  public static function addmoreCallback(array &$form, FormStateInterface $formState): mixed {

    $triggeringElement = $formState->getTriggeringElement();
    [
      $fieldName,
    ] = explode('--', $triggeringElement['#name']);

    return $form[$fieldName];
  }

  /**
   * Delete given attachment from ATV.
   *
   * @param array $fieldValue
   *   Field contents.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state object.
   *
   * @return bool
   *   Result of deletion.
   */
  public static function deleteAttachmentFile(array $fieldValue, FormStateInterface $formState): bool {
    $fieldToRemove = $fieldValue;

    $storage = $formState->getStorage();
    /** @var \Drupal\helfi_atv\AtvDocument $grantsProfileDocument */
    $grantsProfileDocument = $storage['profileDocument'];

    // Try to look for a attachment from document.
    $attachmentToDelete = array_filter(
      $grantsProfileDocument->getAttachments(),
      function ($item) use ($fieldToRemove) {
        if ($item['filename'] == $fieldToRemove['confirmationFileName']) {
          return TRUE;
        }
        return FALSE;
      });

    $attachmentToDelete = reset($attachmentToDelete);
    $hrefToDelete = NULL;

    // If attachment is found.
    if ($attachmentToDelete) {
      // Get href for deletion.
      $hrefToDelete = $attachmentToDelete['href'];
    }
    else {
      // Attachment not found, so we must have just added one.
      $triggeringElement = $formState->getTriggeringElement();
      // Get delta for deleting.
      [$fieldName, $delta] = explode('--', $triggeringElement["#name"]);
      // Upload function has added the attachment information earlier.
      if ($justAddedElement = $storage["confirmationFiles"][(int) $delta]) {
        // So we can just grab that href and delete it from ATV.
        $hrefToDelete = $justAddedElement["href"];
      }
    }

    if (!$hrefToDelete) {
      return FALSE;
    }

    /** @var \Drupal\helfi_atv\AtvService $atvService */
    $atvService = \Drupal::service('helfi_atv.atv_service');
    /** @var \Drupal\helfi_audit_log\AuditLogService $auditLogService */
    $auditLogService = \Drupal::service('helfi_audit_log.audit_log');

    try {
      // Delete attachment by href.
      $deleteResult = $atvService->deleteAttachmentByUrl($hrefToDelete);

      $message = [
        "operation" => "GRANTS_APPLICATION_ATTACHMENT_DELETE",
        "status" => "SUCCESS",
        "target" => [
          "id" => $grantsProfileDocument->getId(),
          "type" => $grantsProfileDocument->getType(),
          "name" => $grantsProfileDocument->getTransactionId(),
        ],
      ];
      $auditLogService->dispatchEvent($message);

    }
    catch (\Throwable $e) {

      $deleteResult = FALSE;

      $message = [
        "operation" => "GRANTS_APPLICATION_ATTACHMENT_DELETE",
        "status" => "FAILURE",
        "target" => [
          "id" => $grantsProfileDocument->getId(),
          "type" => $grantsProfileDocument->getType(),
          "name" => $grantsProfileDocument->getTransactionId(),
        ],
      ];
      $auditLogService->dispatchEvent($message);

      \Drupal::logger('grants_profile')
        ->error('Attachment deletion failed, @error', ['@error' => $e->getMessage()]);
    }

    return $deleteResult;
  }

  /**
   * Handle possible errors after form is built.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   *
   * @return array
   *   Updated form.
   */
  public static function afterBuild(array $form, FormStateInterface &$formState): array {

    $formErrors = $formState->getErrors();

    return $form;
  }

  /**
   * Compare two account numbers.
   *
   * @param string $account1
   *   The 1st account number.
   * @param string $account2
   *   The 2nd account number.
   *
   * @return bool
   *   Are account numbers equal
   */
  protected static function accountsAreEqual(string $account1, string $account2) {
    $account1Cleaned = strtoupper(str_replace(' ', '', $account1));
    $account2Cleaned = strtoupper(str_replace(' ', '', $account2));
    return $account1Cleaned == $account2Cleaned;
  }

  /**
   * Validate bank accounts.
   *
   * To reduce complexity.
   *
   * @param array $values
   *   Form values.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   */
  public function validateBankAccounts(array $values, FormStateInterface $formState): void {
    if (array_key_exists('bankAccountWrapper', $values)) {
      if (empty($values["bankAccountWrapper"])) {
        $elementName = 'bankAccountWrapper]';
        $formState->setErrorByName($elementName, $this->t('You must add one bank account'));
        return;
      }
      $validIbans = [];
      foreach ($values["bankAccountWrapper"] as $key => $accountData) {
        if (!empty($accountData['bankAccount'])) {
          $myIban = new IBAN($accountData['bankAccount']);
          $ibanValid = FALSE;

          if ($myIban->Verify()) {
            // Get the country part from an IBAN.
            $iban_country = $myIban->Country();
            // Only allow Finnish IBAN account numbers..
            if ($iban_country == 'FI') {
              // If so, return true.
              $ibanValid = TRUE;
              $validIbans[] = $myIban->MachineFormat();
            }
          }
          if (!$ibanValid) {
            $elementName = 'bankAccountWrapper][' . $key . '][bank][bankAccount';
            $formState->setErrorByName($elementName, $this->t('Not valid Finnish IBAN: @iban', ['@iban' => $accountData["bankAccount"]]));
          }
        }
        else {
          $elementName = 'bankAccountWrapper][' . $key . '][bank][bankAccount';
          $formState->setErrorByName($elementName, $this->t('You must enter valid Finnish iban'));
        }
        if ((empty($accountData["confirmationFileName"]) && empty($accountData["confirmationFile"]['fids']))) {
          $elementName = 'bankAccountWrapper][' . $key . '][bank][confirmationFile';
          $formState->setErrorByName($elementName, $this->t('You must add confirmation file for account: @iban', ['@iban' => $accountData["bankAccount"]]));
        }
      }
      if (count($validIbans) !== count(array_unique($validIbans))) {
        $elementName = 'bankAccountWrapper]';
        $formState->setErrorByName($elementName, $this->t('You can add an account only once.'));
      }
    }
  }

}
