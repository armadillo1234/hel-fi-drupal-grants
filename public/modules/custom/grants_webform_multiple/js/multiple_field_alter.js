// eslint-disable-next-line no-unused-vars
(($, Drupal, drupalSettings) => {
  Drupal.behaviors.grants_webform_multiple = {
    attach: function attach() {
      $('.webform-multiple-table').each((index, table) => {
        $(table).find('tr').each((index, row) => {
          var removebutton = $(row).find('input[data-drupal-selector*="remove"]');
          removebutton.attr('type', 'button');
          removebutton.attr('src', null);
          removebutton.attr('class', 'hds-button hds-button--primary');
          removebutton.attr('value',Drupal.t('Remove'));
          $('.tabledrag-toggle-weight-wrapper').remove();
          $('.tabledrag-handle').remove();
          $(row).children('td.webform-multiple-table--handle').remove();
          removebutton.appendTo($(row).children('td')[0]);
          $(row).children('td.webform-multiple-table--operations').remove();
        })
      })
    }
  }
  // eslint-disable-next-line no-undef
})(jQuery, Drupal, drupalSettings);
