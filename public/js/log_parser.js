$(document).ready(function () {
    let lineNum = $('[data-line-num]');
    let phpStormLink = $('[data-link]');
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
    phpStormLink.on('click', function () {
        let rawPath = $(this).html();
        let fileLine = $(this).data('file-line');
        let relPath = $(this).html().replace(/\//g, '\\');
        let path = basePath + relPath;
        let html = '';
        if (navigator.userAgent.indexOf('Windows') === -1) {
            let uri = encodeURIComponent(basePath + rawPath + '&line=' + fileLine);
            // https://github.com/aik099/PhpStormProtocol
            html = '<a href="phpstorm://open?file=' + uri + '" target="_blank">' + rawPath + '&line=' + fileLine +  '</a>';

            $("#dialog").html(html).dialog({width: '600px'});
        } else {
            html = '<div id="copyText" class="link">' + path + '</div>';

            $("#dialog").html(html).dialog({width: '600px'});
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
