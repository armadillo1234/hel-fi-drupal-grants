// eslint-disable-next-line no-unused-vars
((Drupal, drupalSettings) => {
  Drupal.behaviors.grants_webform_summation_fieldAccessData = {
    attach: function attach() {
      var fieldName = drupalSettings.fieldName
      var columnName = drupalSettings.columnName
      var fieldIDName = 'edit-'+fieldName+'-items'
      var i = 0
      var continueLoop = true

      while (continueLoop) {
        var myEle = document.getElementById(fieldIDName + '-' + i++ + '-' + columnName);
        if(myEle) {
          myEle.addEventListener("keypress", (event) => {
            var sumFieldName = drupalSettings.sumFieldName
            var fieldName = drupalSettings.fieldName
            var columnName = drupalSettings.columnName
            var fieldIDName = 'edit-'+fieldName+'-items'
            var i = 0
            var continueInnerLoop = true
            var sum = 0;

            while (continueInnerLoop) {
              var myEle = document.getElementById(fieldIDName + '-' + i++ + '-' + columnName);
              if(myEle) {
                myString = 0+myEle.value.replace(/\D/g,'');
                sum += parseInt(myString)
              } else {
                continueInnerLoop = false
              }
            }
            var decimal = (sum%100).toString();
            while (decimal.length < 2) decimal = "0" + decimal;
            document.getElementById(sumFieldName).value = Math.floor(sum/100)+','+decimal+'€'
          });
        } else {
          continueLoop = false
        }
      }

    },
  };
  // eslint-disable-next-line no-undef
})(Drupal, drupalSettings);
