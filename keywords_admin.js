habari.keywords_admin = {
    init_config_form: function() {
        var $form = $('#keywords_configuration');
        $('fieldset fieldset', $form).each(function() {
            var $fieldset = $(this);
            $fieldset.addClass('collapsed');
            $fieldset.bind('click', function() {
                if ($fieldset.hasClass('collapsed')) {
                    $fieldset.removeClass('collapsed');
                } else {
                    $fieldset.addClass('collapsed');
                }
            });
        });
    }
};

$(function() {
    habari.keywords_admin.init_config_form();
});
