<?php

namespace Drupal\grants_metadata\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\ListDataDefinition;
use Drupal\grants_budget_components\TypedData\Definition\GrantsBudgetInfoDefinition;

/**
 * Define Yleisavustushakemus data.
 */
class KuvaProjektiDefinition extends ComplexDataDefinitionBase {

  use ApplicationDefinitionTrait;

  /**
   * Base data definitions for all.
   *
   * @return array
   *   Property definitions.
   */
  public function getPropertyDefinitions(): array {
    if (!isset($this->propertyDefinitions)) {

      $info = &$this->propertyDefinitions;

      foreach ($this->getBaseProperties() as $key => $property) {
        $info[$key] = $property;
      }

      $info['subventions'] = ListDataDefinition::create('grants_metadata_compensation_type')
        ->setLabel('compensationArray')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'compensationArray',
        ])
        ->addConstraint('NotBlank')
        ->setRequired(TRUE)
        ->setSetting('formSettings', [
          'formElement' => 'subventions',
        ]);

      $info['ensisijainen_taiteen_ala'] = DataDefinition::create('string')
        ->setLabel('Ensisijainen taiteenala')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'primaryArt',
        ])
        ->addConstraint('NotBlank')
        ->setRequired(TRUE)
        ->setSetting('formSettings', [
          'formElement' => 'ensisijainen_taiteen_ala',
        ]);

      $info['hankkeen_nimi'] = DataDefinition::create('string')
        ->setLabel('Hankkeen nimi')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'nameOfEvent',
        ])
        ->addConstraint('NotBlank')
        ->setRequired(TRUE)
        ->setSetting('formSettings', [
          'formElement' => 'hankkeen_nimi',
        ]);

      $info['kyseessa_on_festivaali_tai_tapahtuma'] = DataDefinition::create('boolean')
        ->setLabel('Kyseessä on festivaali')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'isFestival',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);

      $info['hankkeen_tai_toiminnan_lyhyt_esittelyteksti'] = DataDefinition::create('string')
        ->setLabel('Hankkeen tai toiminnan lyhyt esittelyteksti')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'purpose',
        ]);

      $info['kokoaikainen_henkilosto'] = DataDefinition::create('integer')
        ->setLabel('Kokoaikainen henkilöstö')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'staffPeopleFulltime',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('formSettings', [
          'formElement' => 'osa_aikainen_henkilosto',
        ])
        ->setSetting('defaultValue', 0)
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['osa_aikainen_henkilosto'] = DataDefinition::create('integer')
        ->setLabel('Osa-aikainen henkilöstö')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'staffPeopleParttime',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ])
        ->setSetting('formSettings', [
          'formElement' => 'osa_aikainen_henkilosto',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('defaultValue', 0);

      $info['vapaaehtoinen_henkilosto'] = DataDefinition::create('integer')
        ->setLabel('Vapaaehtoinen henkilöstö')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'staffPeopleVoluntary',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['kokoaikainen_henkilotyovuosia'] = DataDefinition::create('integer')
        ->setLabel('Kokoaikaisten henkilötyövuodet')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'staffManyearsFulltime',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ])
        ->setSetting('defaultValue', 0);

      $info['osa_aikainen_henkilotyovuosia'] = DataDefinition::create('integer')
        ->setLabel('Osa-aikaisten henkilötyövuodet')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'staffManyearsParttime',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ])
        ->setSetting('defaultValue', 0);

      $info['toiminta_taiteelliset_lahtokohdat'] = DataDefinition::create('string')
        ->setLabel('Kuvaa toiminnan taiteellisia lähtökohtia ja tavoitteita, taiteellista ammattimaisuutta sekä asemaa taiteen kentällä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_taiteelliset_lahtokohdat',
        ]);

      $info['toiminta_tasa_arvo'] = DataDefinition::create('string')
        ->setLabel('Miten monimuotoisuus ja tasa-arvo toteutuu ja näkyy toiminnan järjestäjissä ja organisaatioissa sekä toiminnan sisällöissä? Minkälaisia toimenpiteitä, resursseja ja osaamista on asian edistämiseksi?')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_tasa_arvo',
        ]);

      $info['toiminta_saavutettavuus'] = DataDefinition::create('string')
        ->setLabel('Miten toiminta tehdään kaupunkilaiselle sosiaalisesti, kulttuurisesti, kielellisesti, taloudellisesti, fyysisesti, alueellisesti tai muutoin mahdollisimman saavutettavaksi? Minkälaisia toimenpiteitä, resursseja ja osaamista on asian edistämiseksi?')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_saavutettavuus',
        ]);

      $info['toiminta_yhteisollisyys'] = DataDefinition::create('string')
        ->setLabel('Miten toiminta vahvistaa yhteisöllisyyttä, verkostomaista yhteistyöskentelyä ja miten kaupunkilaisten on mahdollista osallistua toiminnan eri vaiheisiin? Minkälaisia toimenpiteitä, resursseja ja osaamista on asian edistämiseksi?')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_yhteisollisyys',
        ]);

      $info['toiminta_kohderyhmat'] = DataDefinition::create('string')
        ->setLabel('Keitä toiminnalla tavoitellaan? Miten kyseiset kohderyhmät aiotaan tavoittaa ja mitä osaamista näiden kanssa työskentelyyn on?')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_kohderyhmat',
        ]);

      $info['toiminta_ammattimaisuus'] = DataDefinition::create('string')
        ->setLabel('Kuvaa toiminnan järjestämisen ammattimaisuutta ja organisoimista')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_ammattimaisuus',
        ]);

      $info['toiminta_ekologisuus'] = DataDefinition::create('string')
        ->setLabel('Miten ekologisuus huomioidaan toiminnan järjestämisessä? Minkälaisia toimenpiteitä, resursseja ja osaamista on asian edistämiseksi?')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_ekologisuus',
        ]);

      $info['toiminta_yhteistyokumppanit'] = DataDefinition::create('string')
        ->setLabel('Nimeä keskeisimmät yhteistyökumppanit ja kuvaa yhteistyön muotoja ja ehtoja.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_yhteistyokumppanit',
        ]);

      $info['tapahtuma_tai_esityspaivien_maara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Tapahtuma- tai esityspäivien määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'eventDaysCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      /* Esitykset */
      $info['esitykset_maara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'performanceCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['esitykset_maara_kaikkiaan'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'performanceCountAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      /* Näyttelyt */
      $info['nayttelyt_maara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'exhibitionCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['nayttelyt_maara_kaikkiaan'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'exhibitionCountAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      /* Työpajat */
      $info['tyopaja_maara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'workshopCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['tyopaja_maara_kaikkiaan'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'workshopCountAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['kantaesitysten_maara'] = DataDefinition::create('integer')
        ->setLabel('Kantaesitysten määrä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'firstPublicPerformancesCount',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['ensi_iltojen_maara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Ensi-iltojen määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'premiereCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['ensimmainen_yleisolle_avoimen_tilaisuuden_paikka_helsingissa'] = DataDefinition::create('string')
        ->setLabel('Ensimmäisen yleisölle avoimen tilaisuuden paikka Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'firstPublicEventLocationHki',
        ]);

      $info['postinumero'] = DataDefinition::create('string')
        ->setLabel('Postinumero.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'firstPublicEventLocationPostCode',
        ]);

      $info['kyseessa_on_kaupungin_omistama_tila'] = DataDefinition::create('boolean')
        ->setLabel('Kyseessä on kaupungin omistama tila.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'isOwnedByCity',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);

      $info['maara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'eventsVisitorsTotal',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['maara_kaikkiaan'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'eventsVisitorsHkiTotal',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['ensimmaisen_yleisolle_avoimen_tilaisuuden_paivamaara'] = DataDefinition::create('string')
        ->setLabel('Ensimmäisen yleisölle avoimen tilaisuuden päivämäärä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'firstPublicOccasionDate',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'datetime',
        ])
        ->setSetting('valueCallback', [
          'service' => 'grants_metadata.converter',
          'method' => 'convertDates',
          'arguments' => [
            'dateFormat' => 'c',
          ],
        ]);

      $info['hanke_alkaa'] = DataDefinition::create('string')
        ->setLabel('Hanke alkaa.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'projectStartDate',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'datetime',
        ])
        ->setSetting('valueCallback', [
          'service' => 'grants_metadata.converter',
          'method' => 'convertDates',
          'arguments' => [
            'dateFormat' => 'c',
          ],
        ]);
      $info['hanke_loppuu'] = DataDefinition::create('string')
        ->setLabel('Hanke loppuu.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'projectEndDate',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'datetime',
        ])
        ->setSetting('valueCallback', [
          'service' => 'grants_metadata.converter',
          'method' => 'convertDates',
          'arguments' => [
            'dateFormat' => 'c',
          ],
        ]);

      $info['festivaalin_tai_tapahtuman_kohdalla_tapahtuman_paivamaarat'] = DataDefinition::create('string')
        ->setLabel('Tapahtuman tai festivaalin kohdalla tapahtuman päivämäärät.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'eventOrFestivalDates',
        ]);
      $info['laajempi_hankekuvaus'] = DataDefinition::create('string')
        ->setLabel('Laajempi hankekuvaus.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'detailedProjectDescription',
        ]);

      $info['tila'] = ListDataDefinition::create('grants_premises')
        ->setLabel('Tilat')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedPremisesArray',
        ])
        ->setSetting('fullItemValueCallback', [
          'service' => 'grants_premises.service',
          'method' => 'processPremises',
        ])
        ->setSetting('fieldsForApplication', [
          'premiseName',
          'isOwnedByCity',
          'postCode',
        ]);

      $info['organisaatio_kuuluu_valtionosuusjarjestelmaan_vos_'] = DataDefinition::create('boolean')
        ->setLabel('Organisaatio kuuluu valtionosuusjärjestelmään (VOS).')
        ->setSetting('jsonPath', [
          'compensation',
          'budgetInfo',
          'budgetInfoArray',
          'isPartOfVOS',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);

      $info['members_applicant_person_local'] = DataDefinition::create('integer')
        ->setLabel('Helsinkiläisiä henkilöjäseniä yhteensä')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'membersPersonLocal',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['members_applicant_person_global'] = DataDefinition::create('integer')
        ->setLabel('Henkilöjäseniä yhteensä')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'membersPersonGlobal',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['members_applicant_community_local'] = DataDefinition::create('integer')
        ->setLabel('Helsinkiläisiä yhteisöjäseniä yhteensä')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'membersCommunityLocal',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['members_applicant_community_global'] = DataDefinition::create('integer')
        ->setLabel('Yhteisöjäsenet')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'membersCommunityGlobal',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['budgetInfo'] = GrantsBudgetInfoDefinition::create('grants_budget_info')
        ->setSetting('propertyStructureCallback', [
          'service' => 'grants_budget_components.service',
          'method' => 'processBudgetInfo',
          'webform' => TRUE,
        ])
        ->setSetting('webformDataExtracter', [
          'service' => 'grants_budget_components.service',
          'method' => 'extractToWebformData',
          'mergeResults' => TRUE,
        ])
        ->setSetting('jsonPath', ['compensation', 'budgetInfo']);

      $info['muu_huomioitava_panostus'] = DataDefinition::create('string')
        ->setLabel('Muu huomioitava panostus.')
        ->setSetting('jsonPath', [
          'compensation',
          'budgetInfo',
          'budgetInfoArray',
          'otherValuables',
        ]);

    }
    return $this->propertyDefinitions;
  }

  /**
   * Override property definition.
   *
   * @param string $name
   *   Property name.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface|void|null
   *   Property definition.
   */
  public function getPropertyDefinition($name) {
    $retval = parent::getPropertyDefinition($name);
    return $retval;
  }

}
