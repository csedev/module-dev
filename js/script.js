/**
 * @file
 * Javascript file for the module.
 */

Drupal.behaviors.bookVisit = {
  attach: function(context, settings) {
    // Using once() with more complexity.
    // $(context).find('input.myCustom').once('mySecondBehavior').each(function () {
    //   if ($(this).visible()) {
    //       $(this).css('background', 'green');
    //   }
    //   else {
    //     $(this).css('background', 'yellow').show();
    //   }
    // });

    // alert('Library loaded!');
  }
}; 