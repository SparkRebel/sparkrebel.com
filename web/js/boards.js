function truncateBoardName(maxNameLength, newNameLength) {
    $('.boardThumb h3').not('.originalBoardName').each(function() {
        if($(this).parent().children('.originalBoardName').length == 0) {
            $(this).after($(this).clone().css('display', 'none').addClass('originalBoardName'));
        }
        boardName = $(this).parent().children('.originalBoardName').clone().children().remove().end().text();
        if(boardName.length > maxNameLength) {
            $(this).text(boardName.substr(0, newNameLength) + ' ... ');
        } else {
            $(this).text(boardName);
        }
    });
}

function truncateBoardAuthor(maxNameLength, newNameLength) {
    $('.boardThumb .boardAuthor strong').not('.originalBoardAuthor').each(function() {
        if($(this).parent().children('.originalBoardAuthor').length == 0) {
            $(this).after($(this).clone().css('display', 'none').addClass('originalBoardAuthor'));
        }
        authorName = $(this).parent().children('.originalBoardAuthor').clone().children().remove().end().text();
        sparksCount = $(this).parent().parent().children('.boardCount');
        if(authorName.length + sparksCount.text().length > maxNameLength) {
            $(this).text(authorName.substr(0, newNameLength - sparksCount.text().length) + ' ... ');
        } else {
            $(this).text(authorName);
        }
    });
}

if($('#content').width() == 300) {
    truncateBoardName(89, 87);
    truncateBoardAuthor(41, 39);
};

$(window).on('enterBreakpoint720',function() {
    truncateBoardName(44, 42);
    truncateBoardAuthor(10, 8);
});

$(window).on('enterBreakpoint960',function() {
    truncateBoardName(62, 60);
    truncateBoardAuthor(25, 23);
});

$(window).on('enterBreakpoint1200',function() {
    truncateBoardName(77, 75);
    truncateBoardAuthor(37, 35);
});