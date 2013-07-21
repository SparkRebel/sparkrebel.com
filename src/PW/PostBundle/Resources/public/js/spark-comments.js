$(function(){
    $('a.showAllComments').click(function(e){
    	e.preventDefault();
        $('#activityList').animate({ opacity: 0 }, 200);
        $('#commentsLoading').show();
        $.ajax({
            url: $.url('/spark-comments/' + $(this).attr('data-id')),
            'type': 'GET',
            dataType: 'json',
            success: function(data) {
                $('#commentsLoading').hide();
                $('#activityList').html(data.postActivity).animate({ opacity: 1 }, 200);
                $('#commentsCount').text(data.numComments + ' Comments');
                APP.flag.init();
            }
        });
    });
});
