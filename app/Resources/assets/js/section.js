;(function(UI){

    "use strict";

    $("#newSection").on('click', function(){
        // Create new section element in material view
        
        var url = $(this).data('sectionUrl');
        // send ajax request to get new section item
        $.ajax({
            url: url
        })
        .done(function(result) {
            // set section item in material view
            $('.material-section').last().after(result);
            $('.material-section').last()[0].scrollIntoView();
        });
    });

    UIkit.on('changed.uk.dom', function(event) {
        $("#sorting_save").unbind().on('click', function() {
            var sorting = [];
            $(".section-list").children().each(function() {
                var id = $(this).attr('id').match(/([\d]+)/g);
                sorting.push(id);
            });
            
            $.ajax({
                type: "POST",
                dataType: "json",
                url: $(this).data('ukUrl'),
                data: JSON.stringify(sorting)
            })
            .done(function(result) {
            });
            location.reload();
        });
    });

})(UIkit);