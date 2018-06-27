# yii2-module-files

[![Build Status](https://travis-ci.org/floor12/yii2-module-files.svg?branch=master)](https://travis-ci.org/floor12/yii2-module-files)

*This readme is available in [english](README.md).*

Модуль позволяет добавить к ActiveRecord моделям поля с файлами и гибко управлять ими. 

В поставку входят виджет для форм редактирования, а так же виджет для отображения приложенных файлов (его использовать не обязательно).

Установка
------------

#### Ставим модуль

Выполняем команду
```bash
$ composer require floor12/yii2-module-files
```

иди добавляем в секцию "requred" файла composer.json
```json
"floor12/yii2-module-files": "dev-master"
```


#### Выполняем миграцию для созданию необходимых таблиц
```bash
$ ./yii migrate --migrationPath=@vendor/floor12/yii2-module-files/src/migrations/
```

#### Добавляем модуль в конфиг приложения
```php  
'modules' => [
            'files' => [
                'class' => 'floor12\files\Module',
                'editRoles' => ['admin'],
                'storage' => '@vendor/../storage',
                'token_salt' => '!FgGGsdfsef23@Ejhfskj34',
            ],
        ],
    ...
```

Параметры:

- `editRole` - роль пользователей, которым доступно управление. Можно использовать `@`.
- `storage` - алиас пути к хранилищу файлов на диске, по умолчанию располагается в папке storage в корне проекта.
- `token_salt` - уникальная соль для безопасностой работы виджетов.


Использование
-----

### Работа с моделью ActiveRecord
Для подключения модуля к модели `ActiveRecord`, необходимо назначить ей `FileBehaviour` 
и указать, в параметре attributes какие поля с файлами необходимо создать:

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

Как и для других атрибутов модели, указываем ей `attributeLabels()`:

```
 public function attributeLabels()
    {
        return [
            'avatar' => 'Аватар',
            'documents' => 'Документы',
        ];
    }
```

В `rules()` описываем правила валидации:
```php
public function rules()
{
    return [
        ['avatar', 'required],
        ['avatar', 'file', 'extensions' => ['jpg', 'png', 'jpeg', 'gif'], 'maxFiles' => 1, 'ratio'=>1], 
        ['docs', 'file', 'extensions' => ['docx','xlsx], 'maxFiles' => 10],
    ...    
```

Если `maxFiles` будет равен единице, то доступ к объекту `floor12\files\models\File` можно получить напрямую `$model->avatar`. Например:
```php
echo Html::img($model->avatar->href)            // путь к файлу
echo Html::img($model->avatar->hrefPreview)     // путь к миниатюре, если это изображение
echo Html::img($model->avatar)                  // объект приводится к строке, содержащей путь к файлу для удобства
```

В случае, если `maxFiles` > 1 и файлов можно загрузить несколько, то поле будет содержать массив объектов `floor12\files\models\File`:


```php
foreach ($model->docs as $doc}
    Html::a($doc->title, $doc->href);
```
Помимо этого, есть отдельный виджет для вывода всех файлов, который дает возможность просматривать 
изображения в галереи [Lightbox2](https://lokeshdhakar.com/projects/lightbox2/) и осуществлять предпросмотр файлов Office. Так же имеется возможно скачать все приложенные к модели файлы архивом.
 ```php
echo \floor12\files\components\FilesBlock::widget([
    'files' => $model->docs, 
    'title' => 'Приложенные документы:',            // по-умолчанию будет использован Label из модели 
    'downloadAll' => true, 
    'zipTitle' => "docs_of_model_" . $model->id
]) 
```

### Работа c виджетом формы

Во время редактирования модели, необходимо использовать виджет `floor12\files\components\FileInputWidget`:

```php
    <?= $form->field($model, 'avatar')->widget(FileInputWidget::class, []) ?>
    
    <?= $form->field($model, 'docs')->widget(FileInputWidget::class, []) ?>
```
При этом, виджет сам примет нужный вид, в случае добавления одного или нескольких файлов. 
Если указан обязательный `ratio` для изображений, автоматически откроет окно с кропером изображений.

