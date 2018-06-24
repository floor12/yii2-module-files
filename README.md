# yii2-module-files

[![Build Status](https://travis-ci.org/floor12/yii2-module-files.svg?branch=master)](https://travis-ci.org/floor12/yii2-module-files)

*Этот файл доступен на [русском языке](README_RU.md).*
 
This module allows to add files attributes to your ActiveRecord Models.

This module includes widgets for ActiveForm to upload and, crop and edit files, and widget to show files in frontend. 

Installation
------------

#### Installation of module in you app

Just run:
```bash
$ composer require floor12/yii2-module-files
```

or add this to the require section of your composer.json.
```json
"floor12/yii2-module-files": "dev-master"
```


Run the migration to create a table for `File` model:
```bash
$ ./yii migrate --migrationPath=@vendor/floor12/yii2-module-files/src/migrations/
```

Add this to **modules** section
```php  
'modules' => [
        'files' => [
            'class' => 'floor12\files\Module',
            'editRole' => 'admin',
            'storage' = '@vendor/../storage';
            'token_salt' = '!FgGGsdfsef23@Ejhfskj34';
        ],
    ]
    ...
```

Params:

1. `editRole` - user role that allowed to manage files. You can use `@`.
2. `storage` - path alias to folder where files must be stored. Default is *storage* folder in root of your app.
2. `ffmpeg` - system path to Ffmpeg (in case when video files used).
2. `token_salt` - unique salt to protect file edit forms.


Usage
-----

### Work with the ActiveRecord model

To connect the module to the `ActiveRecord` model, you must assign it a `FileBehaviour`
and specify the attributes parameter, what fields with files need to be created:

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

As for the other attributes of the model, specify the `attributeLabels()`:

```
 public function attributeLabels()
    {
        return [
            'avatar' => 'User avatar',
            'documents' => 'Attachments',
        ];
    }
```

 Specify the the validation `rules()`:
```php
public function rules()
{
    return [
        ['avatar', 'required],
        ['avatar', 'file', 'extensions' => ['jpg', 'png', 'jpeg', 'gif'], 'maxFiles' => 1, 'ratio'=>1], 
        ['docs', 'file', 'extensions' => ['docx','xlsx], 'maxFiles' => 10],
    ...    
```

If `maxFiles` is equal to one, then access to the `floor12\files\models\File` object can be obtained directly from $model-> avatar. For example:
```php
echo Html::img($model->avatar->href)            // the web path to file
echo Html::img($model->avatar->hrefPreview)     // the  web path to file preview, if the file is image
echo Html::img($model->avatar)                  // __toString of File returns the web path
```

In case `maxFiles` > 1, for multiple file upload, the attribute will contain an array of objects `floor12\files\models\File`:

```php
foreach ($model->docs as $doc}
    Html::a($doc->title, $doc->href);
```

In addition, there is a widget for displaying all files, which makes it possible to view images in the [Lightbox2](https://lokeshdhakar.com/projects/lightbox2/)  gallery and preview the Office files. It is also possible to download all the files attached to current model attribute archived in ZIP format.

 ```php
echo \floor12\files\components\FilesBlock::widget([
    'files' => $model->docs, 
    'title' => 'Attachments:',            // by default Label from model will used 
    'downloadAll' => true, 
    'zipTitle' => "docs_of_model_" . $model->id
]) 
```

### Widget for ActiveFrom

Use special widget to upload and reorder (both with drug-and-drop), crop and rename files in forms.

```php
    <?= $form->field($model, 'avatar')->widget(FileInputWidget::class, []) ?>
    
    <?= $form->field($model, 'docs')->widget(FileInputWidget::class, []) ?>
```
The widget itself will take the desired form, in the case of adding 1 or more files.
If you specify the required `ratio` for images, it will automatically open the window with the image crapper.

