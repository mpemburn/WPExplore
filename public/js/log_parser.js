$(document).ready(function () {
    let lineNum = $('[data-line-num]');
    let phpStormLink = $('[data-link]');
    lineNum.on('click', function () {
        let lineNum = $(this).data('line-num');
        let logPrefix = $('[data-log]').data('log');
        let lineBox = $('div[data-line-num="' + lineNum + '"');
        let lineText = $('.log-line[data-line-num="' + lineNum + '"]').html();

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
    phpStormLink.on('click', function () {
        let relPath = $(this).html().replace(/\//g, '\\');
        let path = basePath + relPath;
        let html = '';
        if (navigator.userAgent.indexOf('Windows') === -1) {
            // https://github.com/aik099/PhpStormProtocol
            html = '<a href="phpstorm://open?url=file://' + path + '" target="_blank">' + path + '</a>';
        } else {
            html = '<div id="copyText" class="link">' + path + '</div>';

            $("#dialog").html(html).dialog({width: '800px'});
            let copyText = $('#copyText');

            copyText.on('click', function () {
                navigator.clipboard.writeText(relPath.replace(appPath, '')).then(() => {
                    copyText.after('<div id="copied">COPIED</div>');
                    $('#copied').fadeOut(3000);
                }).catch(() => {
                    alert("something went wrong");
                });
            });
        }

    })
});
