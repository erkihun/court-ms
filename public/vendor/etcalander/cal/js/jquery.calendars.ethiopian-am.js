/* http://keith-wood.name/calendars.html
   Amharic localisation for Ethiopian calendar for jQuery v2.1.0.
   Updated for accurate Amharic month/day names. */
(function($) {
    'use strict';
    $.calendars.calendars.ethiopian.prototype.regionalOptions.am = {
        name: 'ኢትዮጵያዊ የቀን መቁጠሪያ',
        epochs: ['ዓ/ም', 'ዓ/ም'],
        monthNames: ['መስከረም', 'ጥቅምት', 'ኅዳር', 'ታህሳስ', 'ጥር', 'የካቲት',
        'መጋቢት', 'ሚያዚያ', 'ግንቦት', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉሜ'],
        monthNamesShort: ['መስከ', 'ጥቅም', 'ኅዳር', 'ታህሳ', 'ጥር', 'የካቲ',
        'መጋቢ', 'ሚያዚ', 'ግንቦ', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉ'],
        dayNames: ['እሑድ', 'ሰኞ', 'ማክሰኞ', 'ረቡዕ', 'ሐሙስ', 'ዓርብ', 'ቅዳሜ'],
        dayNamesShort: ['እሑድ', 'ሰኞ', 'ማክሰ', 'ረቡዕ', 'ሐሙስ', 'ዓርብ', 'ቅዳሜ'],
        dayNamesMin: ['እሑ', 'ሰኞ', 'ማክ', 'ረቡ', 'ሐሙ', 'ዓር', 'ቅዳ'],
        digits: null,
        dateFormat: 'dd/mm/yyyy',
        firstDay: 0,
        isRTL: false
    };
})(jQuery);
