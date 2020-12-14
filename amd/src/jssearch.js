define(['jquery', 'core/str'], function($, str) {
    return {
        init: function () {
            // JS Search Feature.
            // Prep & Get string(s).
            let placeholder = '';
            let string = str.get_string('placeholder', 'enrol_selma');
            $.when(string).done(function(localstring) {
                $('.usersearch').attr('placeholder', localstring);
                placeholder = localstring;
            });

            // Check if on right page.
            let isselmapage = $('#page-enrol-selma-clarity');
            if (isselmapage.length > 0) {
                let isoverviewpage = isselmapage.hasClass('overview');
                let inserthtml = '<input type="text" class="form-control usersearch" placeholder="' + placeholder + '"/>';
                let target = '';
                let searchtarget = '';

                // Check which element to target.
                if (isoverviewpage) {
                    target = isselmapage.find('#accordion');
                    searchtarget = target.find('> .card');

                } else {
                    target = isselmapage.find('#page .table-responsive');
                    searchtarget = target.find('> table > tbody tr');
                }

                // Searchbox container.
                let container = $(target).prepend(inserthtml);
                let jssearch = container.find('.usersearch');

                // Search on key-up.
                $(jssearch).on('keyup', function () {
                    let searchtext = $(jssearch)[0].value.toLowerCase();
                    $(searchtarget).each(function () {
                        if ($(this).text().toLowerCase().includes(searchtext)) {
                            $(this).removeClass("notresult");
                        } else {
                            $(this).addClass("notresult");
                        }
                    });
                });
            }
        }
    }
});
