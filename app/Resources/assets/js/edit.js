;(function(UI) {

    "use strict";

    let partMapping = {
        'material': 'section',
        'todo': 'step',
        'discussion': 'discarticle'
    };

    UI.component('edit', {

        defaults: {
            editUrl: ''
        },

        boot: function() {
            // init code
            UI.ready(function(context) {
                UI.$("[data-cs-edit]", context).each(function() {
                    let element = UI.$(this);

                    if (!element.data("edit")) {
                        let obj = UI.edit(element, UI.Utils.options(element.attr("data-cs-edit")));
                    }
                });
            });
        },

        init: function() {
            let $this = this;

            let element = $this.element[0];

            // look for div.cs-edit and show on mouseover
            $(element)
                .mouseover(function() {
                    $(this).find('div.cs-edit').toggleClass('uk-invisible', false);
                })
                .mouseout(function() {
                    $(this).find('div.cs-edit').toggleClass('uk-invisible', true);
                });

            $this.registerArticleEvents(element);

            if ($this.options.draft == '1') {
                $this.onClickEdit($(element).find('div.cs-edit'));
            }
        },

        registerArticleEvents: function(element) {
            let $this = this;

            $(element).find('div.cs-edit').find('a').attr('data-uk-tooltip', '');
            $(element).find('div.cs-edit').find('a').attr('title', $(element).find('div.cs-edit').data('edit-title'));

            // show articles as selected, when mouseover the edit icon
            $(element).find('div.cs-edit')
                .mouseover(function() {
                    $(this).parents(".cs-edit-section").find(".fade-preview").toggleClass("uk-hidden", true);
                    $(this).parents('.cs-edit-section').toggleClass('cs-selected', true);
                })
                .mouseout(function() {
                    $(this).parents(".cs-edit-section").find(".fade-preview").toggleClass("uk-hidden", false);
                    $(this).parents('.cs-edit-section').toggleClass('cs-selected', false);
                });

            // send ajax requests on click to load the form
            $(element).find('div.cs-edit').click(function(event) {
                event.preventDefault();

                // reset article selection class and remove event handling
                $(this).parents('.cs-edit-section').toggleClass('cs-selected', false);
                $(this).off();

                $this.onClickEdit(this);
            });
        },

        onClickEdit: function(el) {
            let $this = this;
            let article = $(el).parents('.cs-edit-section');

            // show the loading spinner
            $(article).find('.cs-edit-spinner').toggleClass('uk-hidden', false);

            let editButtons = $('.cs-edit');
            editButtons.removeClass('cs-edit');
            editButtons.each(function(){
                $(this).find('a').attr('title', 'close the current form to edit this section');
            });

            $("#cs-additional-actions")
                .addClass('uk-hidden')
                .parent().find("button.uk-button").addClass("uk-text-muted");

            // send ajax request to get edit html
            $.ajax({
              url: this.options.editUrl
            })
            .done(function(result) {
                // replace article html
                article.html($(result));

                $this.handleFormSubmit(article);

            });
        },

        handleFormSubmit: function(article) {
            let $this = this;

            // override form submit behaviour
            article.find('button').click(function (event) {
                let $button = $(this);

                // cancel is not handled via ajax
                if ($button.attr('name').includes('cancel')) {
                    event.preventDefault();
                    /*
                    // cancel editing a NEW entry => return to list view
                    if($("#breadcrumb-nav .current.last").text().trim() == "") {
                        let pathParts = window.location.pathname.split("/");
                        pathParts.pop();
                        window.location.href = pathParts.join("/");
                    }
                    // cancel editing an EXISTING entry => return to detail view of the entry
                    else {
                        // trigger reload of the current URL
                        // We are using the Location.reload() method, since
                        // setting window.location.href might not result in a reload, if
                        // there is an anchor currently set
                        window.location.reload(true);
                    }
                    */
                    // request backend to remove edit lock
                    $.ajax({
                        url: $this.options.cancelEditUrl,
                        type: "POST",
                        data: null
                    })
                    .always(function(result, statusText, xhrObject) {
                        // cancel editing a NEW entry => return to list view
                        if($("#breadcrumb-nav .current.last").text().trim() == "") {
                            let pathParts = window.location.pathname.split("/");
                            pathParts.pop();
                            window.location.href = pathParts.join("/");
                        }
                        // cancel editing an EXISTING entry => return to detail view of the entry
                        else {
                            // trigger reload of the current URL
                            // We are using the Location.reload() method, since
                            // setting window.location.href might not result in a reload, if
                            // there is an anchor currently set
                            window.location.reload(true);
                        }
                    });
                } else {
                    if (!$button.attr('name').includes('newHashtagAdd')) {
                        let form = $(this).closest('form');
                        if (form[0].checkValidity()) {
                            event.preventDefault();

                            let formData = form.serializeArray();
                            formData.push({name: this.name, value: this.value});

                            // submit the form manually
                            $.ajax({
                                url: $this.options.editUrl,
                                type: "POST",
                                data: formData
                            })
                                .done(function (result, statusText, xhrObject) {
                                    let $result = $(result);

                                    if ($result.find('ul.form-errors').length) {
                                        article.html($result);
                                        $this.handleFormSubmit(article);
                                    } else {
                                        window.location.reload(true);
                                    }
                                });
                        }
                    }
                }
            });
        }
    });

})(UIkit);