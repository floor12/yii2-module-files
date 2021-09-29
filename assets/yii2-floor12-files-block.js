function filesDownloadAll(title, event, yiiDownloadAllLink) {
    obj = $(event.target).parents('div.files-block');
    hashes = "";
    $.each(obj.find('.f12-file-object'), function (key, val) {
        hashes += "&hash[]=" + $(val).data('hash');
    });
    console.log(hashes);

    window.open(yiiDownloadAllLink + "?title=" + title + hashes);

}