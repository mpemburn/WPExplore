$(document).ready(function () {
    let lineNum = $('[data-line-num]');
    let linkLineNum = $('[data-link-line-num]');
    let logLine = $('[data-log-line]');

    lineNum.on('click', function () {
        let lineNum = $(this).data('line-num');
        let logPrefix = $('[data-log]').data('log');
        let lineBox = $('div[data-line-num="' + lineNum + '"');
        let lineText = $('.log-line-hidden[data-line-num="' + lineNum + '"]').html();

        lineBox.toggleClass('done', !lineBox.hasClass('done'))

        $.ajax({
            url: 'api/parser',
            type: 'POST',
            dataType: 'json',
            data: {
                lineNum: lineNum,
                lineText: lineText,
                logPrefix: logPrefix
            },
            success: function (response) {
                console.log(response);
            },
            error: function (response) {
                alert(response);
            }
        });
    });

    linkLineNum.on('click', function () {
       let lineNum = $(this).data('link-line-num');
       logLine.each(function () {
           $(this).removeClass('highlight');
       });
       $('[data-log-line="' + lineNum + '"]').addClass('highlight');
    });
});
