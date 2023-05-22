<?php

namespace Drupal\grants_metadata\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\ListDataDefinition;
use Drupal\grants_budget_components\TypedData\Definition\GrantsBudgetInfoDefinition;

/**
 * Define Yleisavustushakemus data.
 */
class KuvaKehaDefinition extends ComplexDataDefinitionBase {

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
        ]);

      $info['ensisijainen_taiteen_ala'] = DataDefinition::create('string')
        ->setLabel('Ensisijainen taiteenala')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'primaryArt',
        ]);

      $info['hankkeen_nimi'] = DataDefinition::create('string')
        ->setLabel('Hankkeen nimi')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'nameOfEvent',
        ]);

      $info['hankkeen_nimi'] = DataDefinition::create('string')
        ->setLabel('Hankkeen nimi')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'nameOfEvent',
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
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
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
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
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
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

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
        ]);

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
        ]);

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

      $info['festivaalin_tai_tapahtuman_paivamaarat'] = DataDefinition::create('string')
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

      $info['sisaltyyko_toiminnan_toteuttamiseen_jotain_muuta_rahanarvoista_p'] = DataDefinition::create('string')
        ->setLabel('Sisältyykö toiminnan toteuttamiseen jotain muuta rahanarvoista panosta tai vaihtokauppaa, joka ei käy ilmi budjetista?')
        ->setSetting('jsonPath', [
          'compensation',
          'budgetInfo',
          'budgetInfoArray',
          'otherValuables',
        ]);

      $info['organisaatio_kuuluu_valtionosuusjarjestelmaan_vos_'] = DataDefinition::create('boolean')
        ->setLabel('Organisaatio kuului valtionosuusjärjestelmään (VOS)')
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
    }

    $info['vuodet_joille_monivuotista_avustusta_on_haettu_tai_myonetty'] = DataDefinition::create('string')
      ->setLabel('Tulevat vuodet joiden ajalle monivuotista avustusta on haettu tai myönnetty')
      ->setSetting('jsonPath', [
        'compensation',
        'compensationInfo',
        'generalInfoArray',
        'yearsForMultiYearApplication',
      ])
      ->setSetting('webformDataExtracter', [
        'service' => 'grants_metadata.atv_schema',
        'method' => 'returnRelations',
        'mergeResults' => TRUE,
        'arguments' => [
          'relations' => [
            'slave' => 'kyseessa_on_monivuotinen_avustus',
            'master' => 'vuodet_joille_monivuotista_avustusta_on_haettu_tai_myonetty',
            'type' => 'boolean',
          ],
        ],
      ]);

    $info['erittely_kullekin_vuodelle_haettavasta_avustussummasta'] = DataDefinition::create('string')
      ->setLabel('Erittely kullekin vuodelle haettavasta avustussummasta.')
      ->setSetting('jsonPath', [
        'compensation',
        'compensationInfo',
        'generalInfoArray',
        'breakdownOfYearlySums',
      ]);

    $info['members_applicant_person_global'] = DataDefinition::create('string')
      ->setLabel('Henkilöjäsenet')
      ->setSetting('defaultValue', "")
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

    $info['members_applicant_person_local'] = DataDefinition::create('string')
      ->setLabel('Näistä helsinkiläisiä')
      ->setSetting('defaultValue', "")
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

    $info['members_applicant_community_global'] = DataDefinition::create('string')
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

    $info['members_applicant_community_local'] = DataDefinition::create('string')
      ->setLabel('Näistä helsinkiläisiä')
      ->setSetting('defaultValue', "")
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
