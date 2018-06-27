console.log('Yii2 files model init.');


var currentCroppingImageId;
var currentRenamingFileId;
var cropper;
var removeFileOnCropCancel;

$(document).on('change', '.yii2-files-upload-field', function () {

    obj = $(this);

    var formData = new FormData();
    formData.append('file', obj[0].files[0]);
    formData.append('modelClass', obj.data('modelclass'));
    formData.append('attribute', obj.data('attribute'));
    formData.append('mode', obj.data('mode'));
    formData.append('ratio', obj.data('ratio'));
    formData.append('_fileFormToken', yii2FileFormToken);


    $.ajax({
        url: yii2UploadRoute,
        type: 'POST',
        data: formData,
        processData: false,  // tell jQuery not to process the data
        contentType: false,  // tell jQuery not to set contentType
        success: function (response) {
            id = '#files-widget-block_' + obj.data('block');
            $(response).appendTo(id).find('div.floor12-files-widget-list');
        }
    });
})

function Yii2FilesUploaderSet(id, className, attribute, scenario) {

    var mode = 'multi';
    var blockName = "#" + id;
    var block = $(blockName);
    var uploadButton = block.find('button.btn-upload')[0];
    var filesList = block.find('.floor12-files-widget-list')[0];
    var ratio = 0;

    if (block.data('ratio'))
        ratio = block.data('ratio');

    if (block.hasClass('floor12-files-widget-single-block')) {
        mode = 'single';
        toggleSingleUploadButton(block);
    }


    var uploader = new ss.SimpleUpload({
        button: uploadButton,
        url: yii2UploadRoute,
        name: 'file',
        dropzone: block,
        dragClass: 'floor12-files-widget-block-drug-over',
        multiple: true,
        multipleSelect: true,
        data: {
            modelClass: className,
            attribute: attribute,
            scenario: scenario,
            mode: mode,
            ratio: ratio,
            _fileFormToken: yii2FileFormToken
        },
        onSubmit:

            function (filename, extension, data) {
                var fileBlock = $('<div>').addClass('floor12-file-object').append($('<div>').addClass('floor12-file-object-uploading')).attr('id', generateId(filename));

                if (mode == 'single') {
                    $(filesList).html('')
                    fileBlock.appendTo(filesList);
                } else
                    fileBlock.appendTo(filesList);
            },
        onComplete: function (filename, response) {
            if (!response) {
                console.log(filename + 'upload failed');
                return false;
            }

            info(FileUploadedText, 1);
            idName = "#" + generateId(filename);
            $(idName).replaceWith($(response));

            if (mode == 'single')
                toggleSingleUploadButton(block);
        },
        onError: function (filename, errorType, status, statusText, response, uploadBtn, fileSize) {
            console.log(1);
            data = {
                responseText: response,
                status: status,
                statusText: statusText,
            };

            processError(data);

            idName = "#" + generateId(filename);
            $(idName).remove();
        }

        //     progressUrl: 'uploadProgress.php', // enables cross-browser progress support (more info below)
        //     responseType: 'json',
        //    allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
        //   maxSize: 1024, // kilobytes
        //     hoverClass: 'ui-state-hover',
        //     focusClass: 'ui-state-focus',
        //     disabledClass: 'ui-state-disabled',
    });

}

function generateId(filename) {
    return filename.replace(/[^a-zA-Z]/g, "");
}

function showUploadButton(event) {
    obj = $(event.target);
    console.log(obj);
    obj.parents('div.floor12-files-widget-single-block').find('button').show();
}

function toggleSingleUploadButton(block) {
    if (block.find('div.floor12-single-file-object').length > 0)
        block.find('button').hide();
    else
        block.find('button').show();
}

function sortableFiles() {
    $(".floor12-files-widget-list-multi").sortable({
        opacity: 0.5,
        revert: 1,
        items: "div.files-btn-group",
    });
}

function removeFile(id) {
    id = "#yii2-file-object-" + id;

    $(id).parents('div.files-btn-group').fadeOut(200, function () {
        $(this).remove();
        info(FileRemovedText, 1);
    });
    return false;
}

function removeAllFiles(event) {
    console.log('removeAllFiles');
    $(event.target).parents('div.floor12-files-widget-list').find('div.files-btn-group').fadeOut(200, function () {
        $(this).remove();
    });
    info(FilesRemovedText, 1);
    return false;
}

function initCropperLayout() {
    $.get(yii2CropperRoute, function (response) {
        $('body').append(response);

        $('#yii2-file-title-editor input').on('keyup', function (e) {
            if (e.keyCode == 13) {
                saveFileTitle()
            }
        });
    })
}

function initCropper(id, url, ratio, remove) {
    $('#cropperModal').modal();

    currentCroppingImageId = id;

    removeFileOnCropCancel = false;
    if (remove)
        removeFileOnCropCancel = true;

    currentCropImage = $('<img>').attr('src', url);
    $('#cropperArea').html("");
    $('#cropperArea').append(currentCropImage);

    autoCrop = false;
    aspectRatio = NaN;

    if (ratio) {
        autoCrop = true;
        aspectRatio = ratio;
    }

    setTimeout(function () {
        cropper = currentCropImage.cropper({
            viewMode: 1,
            background: false,
            zoomable: false,
            autoCrop: autoCrop,
            aspectRatio: aspectRatio,
        });
    }, 1000)
}

function stopCrop(id) {
    $('#cropperModal').modal('hide');
    if (removeFileOnCropCancel)
        removeFile(currentCroppingImageId);
}

function cropImage() {
    сropBoxData = cropper.cropper('getCropBoxData');
    imageData = cropper.cropper('getImageData');
    canvasData = cropper.cropper('getCanvasData');
    ratio = imageData.height / imageData.naturalHeight;
    cropLeft = (сropBoxData.left - canvasData.left) / ratio;
    cropTop = (сropBoxData.top - canvasData.top) / ratio;
    cropWidth = сropBoxData.width / ratio;
    cropHeight = сropBoxData.height / ratio;
    rotated = imageData.rotate;

    data = {
        id: currentCroppingImageId,
        width: cropWidth,
        height: cropHeight,
        top: cropTop,
        left: cropLeft,
        rotated: rotated,
        _fileFormToken: yii2FileFormToken
    }

    removeFileOnCropCancel = false;

    $.ajax({
        url: yii2CropRoute,
        'method': 'POST',
        data: data,
        success: function (response) {
            id = '#yii2-file-object-' + currentCroppingImageId;
            $(id).css('background-image', 'none');
            $(id).css('background-image', 'url(' + response + ')');
            stopCrop();
            info(FileSavedText, 1);
        },
        error: function (response) {
            processError(response);
        }
    })
}

function showRenameFileForm(id, event) {
    var blockId = '#yii2-file-object-' + id
    var title = $(blockId).attr('title');
    currentRenamingFileId = id;
    $('#yii2-file-title-editor').css('top', event.clientY).css('left', event.clientX - 70).fadeIn(100);
    $('#yii2-file-title-editor input').val(title).focus();
}

function hideYii2FileTitleEditor() {
    $('#yii2-file-title-editor').fadeOut(100);
    currentRenamingFileId = null;
}

function saveFileTitle() {
    $('#yii2-file-title-editor').fadeOut(100);
    val = $('#yii2-file-title-editor input').val();
    blockId = '#yii2-file-object-' + currentRenamingFileId;
    $(blockId).attr('title', val)
    $(blockId).attr('data-title', val)

    $.ajax({
            url: yii2RenameRoute,
            method: 'POST',
            data: {id: currentRenamingFileId, title: val, _fileFormToken: yii2FileFormToken},
            success: function () {
                info(FileRenamedText, 1);
            },
            error: function (response) {
                processError(response);
            }
        }
    );
    currentRenamingFileId = null;
}

$(document).ready(function () {
    setInterval(function () {
        sortableFiles()
    }, 2000)

    sortableFiles();

    initCropperLayout();

});


