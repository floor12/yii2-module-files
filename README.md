# yii2-module-files

[![Build Status](https://travis-ci.org/floor12/yii2-module-files.svg?branch=master)](https://travis-ci.org/floor12/yii2-module-files)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/floor12/yii2-module-files/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/floor12/yii2-module-files/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/floor12/yii2-module-files/v/stable)](https://packagist.org/packages/floor12/yii2-module-files)
[![Latest Unstable Version](https://poser.pugx.org/floor12/yii2-module-files/v/unstable)](https://packagist.org/packages/floor12/yii2-module-files)
[![Total Downloads](https://poser.pugx.org/floor12/yii2-module-files/downloads)](https://packagist.org/packages/floor12/yii2-module-files)
[![License](https://poser.pugx.org/floor12/yii2-module-files/license)](https://packagist.org/packages/floor12/yii2-module-files)

*Этот файл доступен на [русском языке](README_RU.md).*

## About the module

![FileInputWidget](https://floor12.net/files/default/get?hash=7ad1b9dee10bb7cb5bd73d1c874724e1)

This module was designed to solve the problem of creating file fields in ActiveRecord models of the Yii2 framework. The
main components of the module are:

- `floor12\files\components\FileBehaviour` - behavior that must be connected to the ActiveRecord model;
- `floor12\files\components\FileInputWidget` - an InputWidget that allows you to add, edit and generally work with
  files;
- `floor12\files\components\FileListWidget` - an additional widget to display a list of files with the abilities to view
  images in the Lightbox2 gallery, download all files of the current field in zip format, and view the Word and Excel
  files using the Microsoft office online.

### Key features

- adding one or more fields with files to the ActiveRecord model;
- setting up validation of these fields using the standard `FileValidator` defined in the` rules ()`section;
- in the case of working with images - the ability to configure the image ratio (in this case, when loading an image
  through the
  `FileInputWidget` widget will automatically open a modal window to crop the image with the desired ratio);
- thumbnails creating with optimal sizes for each case in site template. Also, these thumbnails supports WEBP format;
- download files in ZIP-format
- `FileInputWidget` supports changing of files order by drag-and-drop, cropping and filename updating;
- in case of drag-and-drop uploading, the file result file order is the same as on client folder;
- automatic horizon detection by EXIF ​​tag;
- if you need to add images to the model not with the web interface of the site, but using console parsers and other
  similar cases - its possible. For this case, the module includes two classes: `FileCreateFromInstance`
  and` FileCreateFromPath` with helps add files to AR model from server file system;
- in case of video files: recoding them to h264 using the ffmpeg utility;

### i18n

At this stage, the module supports the following languages:

- English
- Russian

### Principle of operation

All files data is stored in the `file` table. The `file` model relay to the model by three fields:

- `class` - the full class name of the relay model
- `field` - the name of the model field
- `object_id` - primary key of the model

When file added to the form, it uploads to server in background where all processing takes place. As a result of this
processing, it is written to disk and a new entry is created for it in the `file` table, with the fields` class`  and
`field` filled with data from the model, and` object_id` is empty and will assign only after saving the ActiveRecord
model. When a file is deleted from the widget, it is not deleted from the disk and the `file` table, just `obejct_id`
equals to 0. You can use the console command` files / console / clean` to periodically clean up this kind of orphan
files.

## Install and setup

To add this module to your app, just run:

 ```bash
 $ composer require floor12/yii2-module-files
 ```

or add this to the `require` section of your composer.json.

 ```json
 "floor12/yii2-module-files": "dev-master"
 ```

Then execute a migration to create `file` table.

 ```bash
 $ ./yii migrate --migrationPath=@vendor/floor12/yii2-module-files/src/migrations/
 ```

After that, include module data in `modules` section of application config:

 ```php  
 'modules' => [
             'files' => [
                 'class' => 'floor12\files\Module',
                 'storage' => '@app/storage',
                 'cache' => '@app/storage_cache',
                 'token_salt' => 'some_random_salt',
             ],
         ],
     ...
 ```

Parameters to set:

- `storage` - the path alias to the folder to save files and image sources, by default it is located in the `storage`
  folder in the project root;
- `cache` - path alias to the folder of thumbnails of images that the module creates on the fly upon request and caches;
- `token_salt` - a unique salt to generate InputWidget tokens.

## Usage

### Work with ActiveRecord Model

To add one or more files fields to the ActiveRecord model, you need to connect `floor12\files\components\FileBehaviour`
to it and pass list the field names that will store the files in the model. For example, for the User model, 2 file
fields are defined here
: `avatar` and` documents`:

 ```php 
  public function behaviors()
  {
      return [
          'files' => [
              'class' => 'floor12\files\components\FileBehaviour',
              'attributes' => [
                  'avatar',
                  'documents'
              ],
          ],
          ...
 ```

To have nice attribute labels in forms, add some labels to `attributeLabels()`:

 ```php
  public function attributeLabels()
     {
         return [
             ...
             'avatar' => 'Аватар',
             'documents' => 'Документы',
             ...
         ];
     }
 ```

Setup validation rules in the `rules()` method of ActiveRecord model:

 ```php
 public function rules()
 {
     return [
         // Avatar is required attribute 
         ['avatar', 'required'],
         
         // Avatar allow to uploade 1 file with this extensions: jpg, png, jpeg, gif
         ['avatar', 'file', 'extensions' => ['jpg', 'png', 'jpeg', 'gif'], 'maxFiles' => 1], 
 
         // Documens allows to upload a few files with this extensions: docx, xlsx
         ['documents', 'file', 'extensions' => ['docx', 'xlsx'], 'maxFiles' => 10],
     ...    
 ```

### Work with files

If `maxFiles`  in `FileValidator` equals to 1, this attribute will store an `floor12\files\models\File object`. Example:

 ```php
 // The href field contains web path to file source
 echo Html::img($model->avatar->href)     
 
 // __toString() method of File object will return href as well
 echo Html::img($model->avatar)          
 ```

If the file is image, getPreviewWebPath method returns a web path to image thumbnail. By default thumbnail created with
the jpeg or png format, it depends to source file. But also WEBP option is available.

`File::getPreviewWebPath(int $width = 0, int $height = 0 ,bool $webp = false)`

Usage example:

 ```php
 // User avatar thumbnail with 200px width 
 echo Html::img($model->avatar->getPreviewWebPath(200));     
 
 // User avatar thumbnail with 200px width  and WEBP format
 echo Html::img($model->avatar->getPreviewWebPath(200, 0, true));     
      
 ```

When `maxFiles` equals to 1, multiple upload is available. In this case, model field will contains an array
if  `floor12\files\models \File` objects:

 ```php
 foreach ($model->docs as $doc}
     Html::a($doc->title, $doc->href);
 ```

Here is another example, the advanced usage of thumbnails. In this case, we use modern `picture` and` source` tags, as
well as media queries. As a result, we have 8 different thumbnails: 4 has webp format for those browsers that support
this it, and 4 has jpeg format. Devices with retina displays will get an images with double resolution, regular screens
have regular sized pictures. This example also uses different images widths at different screen widths (just as example
of mobile/desktop image switching):

 ```php
 <picture>
     <source type="image/webp" media='(min-width: 500px)' srcset="
                              <?= $model->poster->getPreviewWebPath(150, true) ?> 1x,
                              <?= $model->poster->getPreviewWebPath(300, true) ?> 2x">
     <source type="image/webp" media='(max-width: 500px)' srcset="
                              <?= $model->poster->getPreviewWebPath(350, true) ?> 1x,
                              <?= $model->poster->getPreviewWebPath(700, true) ?> 2x">
     <source type="image/jpeg" media='(min-width: 500px)' srcset="
                              <?= $model->poster->getPreviewWebPath(150, false) ?> 1x,
                              <?= $model->poster->getPreviewWebPath(300, false) ?> 2x">
     <img src="<?= $model->poster->getPreviewWebPath(150) ?>" 
          srcset=" 
                <?= $model->poster->getPreviewWebPath(350) ?> 1x, 
                <?= $model->poster->getPreviewWebPath(700) ?> 2x"
          alt="<?= $model->title ?>">
 </picture>
 ```

### Picture tag widget

If object if tyle `File` is image (`$file->isImage() === true`), it can be used with `PictureWidget`. This widget helps
generate html tag <picture> with srcset with 2x and webp versions. For example this code:

```php
echo PictureWidget::widget([
    'model' => $file,
    'alt' => 'Some alternative text',
    'width' => 100
]);
```

will make this html:

```html

<picture>
    <source type="image/webp"
            srcset="/imageurl/image1xWebp 1x,/imageurl/image2xWebp 2x">
    <img src="/imageurl/image 1x"
         srcset="/imageurl/image 1x,/imageurl/image 2x"
         alt="<?= $model->title ?>">
</picture>
```

Additional parameters allowed to pass media-queries to widget.

### Listing the files

There is a widget for listing all files. It supports [Lightbox2](https://lokeshdhakar.com/projects/lightbox2/) gallery
to display images and MS Office files preview. Its also supports downloading of the all the files attached to the field
in a ZIP-archive.

  ```php
 echo \floor12\files\components\FileListWidget::widget([
     'files' => $model->docs, 
     'downloadAll' => true, 
     'zipTitle' => "Documents of {$user->fullname}" 
 ]) 
 ```

An array of `File` objects must be passed to the widget `files` field. Also additional parameters available:

- `title` - optionally set the title of the block (by default its taken from `AttributeLabels()`)",
- `downloadAll` - show the "download all" button,
- `zipTitle` - set the file name of zip archive,
- `passFirst` - skip first file in array (it is often necessary to display the gallery without the first picture. For
  example, in the news view page, when the first image used to be news main image).

![FileInputWidget](https://floor12.net/files/default/get?hash=6482fa93391f5fdcbbf8eb8d242da684)

### InputWidget for ActiveFrom

To display files block in your forms use the `floor12\files\components\FileInputWidget`:

 ```php
<?= $form->field($model, 'avatar')->widget(floor12\files\components\FileInputWidget::class) ?>
 ```

Moreover, if `maxFiles` parameter in` FileValidator` equals to 1 or more, `FileInputWidget` will take the necessary form
to load one file or several at once. If necessary, you can pass `uploadButtonText` and` uploadButtonClass` parameters to
the widget.

## Contributing

I will be glad of any help in the development, support and bug reporting of this module.