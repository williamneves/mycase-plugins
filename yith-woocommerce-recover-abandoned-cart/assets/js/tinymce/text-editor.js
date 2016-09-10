/**
 * Created by Your Inspiration on 02/07/2015.
 */
(function() {
    tinymce.PluginManager.add('tc_button', function( editor, url ) {
        editor.addButton( 'tc_button', {
            text: false,
            type: 'menubutton',
            icon: 'ywrac-icon',
            menu: [
                {
                    text: editor.getLang('tc_button.firstname'),
                    value: '{{ywrac.firstname}}',
                    onclick: function() {
                        editor.insertContent(this.value());
                    }
                },
                {
                    text: editor.getLang('tc_button.lastname'),
                    value: '{{ywrac.lastname}}',
                    onclick: function() {
                        editor.insertContent(this.value());
                    }
                },
                {
                    text: editor.getLang('tc_button.username'),
                    value: '{{ywrac.username}}',
                    onclick: function() {
                        editor.insertContent(this.value());
                    }
                },
                {
                    text: editor.getLang('tc_button.cartcontent'),
                    value: '{{ywrac.cart}}',
                    onclick: function() {
                        editor.insertContent(this.value());
                    }
                }
            ]

        });
    });

})();