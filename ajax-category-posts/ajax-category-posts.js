jQuery(function($) {
    $(document).on('click', '.ajax-pagination a', function(e) {
        e.preventDefault();

        var $wrapper = $(this).closest('.ajax-posts-wrapper');
        var cat_id = $wrapper.data('cat');
        var ppp_detail = $wrapper.data('ppp');
        var page = $(this).data('page') || 1;

        $.ajax({
            url: ajax_cat_posts.ajax_url,
            type: 'POST',
            data: {
                action: 'load_category_posts',
                cat_id: cat_id,
                paged: page,
                ppp: ppp_detail
            },
            beforeSend: function() {
                $wrapper.addClass('loadingpost');
            },
            success: function(response) {
                $wrapper.removeClass('loadingpost');
                $wrapper.replaceWith(response);
            }
        });
    });
});
