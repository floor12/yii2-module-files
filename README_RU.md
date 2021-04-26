# yii2-module-files

[![Build Status](https://travis-ci.org/floor12/yii2-module-files.svg?branch=master)](https://travis-ci.org/floor12/yii2-module-files)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/floor12/yii2-module-files/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/floor12/yii2-module-files/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/floor12/yii2-module-files/v/stable)](https://packagist.org/packages/floor12/yii2-module-files)
[![Latest Unstable Version](https://poser.pugx.org/floor12/yii2-module-files/v/unstable)](https://packagist.org/packages/floor12/yii2-module-files)
[![Total Downloads](https://poser.pugx.org/floor12/yii2-module-files/downloads)](https://packagist.org/packages/floor12/yii2-module-files)
[![License](https://poser.pugx.org/floor12/yii2-module-files/license)](https://packagist.org/packages/floor12/yii2-module-files)

## Информация о модуле

![FileInputWidget](https://floor12.net/files/default/get?hash=7ad1b9dee10bb7cb5bd73d1c874724e1)

Это модуль разработан для того, чтобы решить проблему создание полей с файлами в ActiveRecord моделях фреймворка Yii2.
Основными компонентами модуля являются:

- `floor12\files\components\FileBehaviour` - поведение, которое необходимо подключить к ActiveRecord модели;
- `floor12\files\components\FileInputWidget` - виджет для формы, позволяющий добавлять, редактировать и в целом работать
  с добавленными файлами;
- `floor12\files\components\FileListWidget` - дополнительный виджет для вывода списка файлов с возможностями просмотра
  изображений в галереи Lightbox2, загрузке всех файлов текущего поля в формате zip, а так же просмотром Word и Excel
  файлов с помощью онлайн офиса от Microsoft.

### Основные функции модуля

- добавление одного и более полей с файлами к ActiveRecord модели;
- настройка валидация этих полей при помощи стандартного `FileValidator`, указанного в секции `rules()`;
- в случае работы с изображениями - возможность сконфигурировать пропорции изображения (в этом случае при загрузке
  изображения через `FileInputWidget` виджет автоматически откроет окно для обрезки изображения с нужными пропорциями);
- возможность создания миниатюр для использования в различных местах шаблонов изображений оптимальных размеров. Так же
  эти миниатюры могут поддерживают формат WEBP;
- возможность скачивания всех добавленных в одно поле файлов в виде ZIP-архива
- при загрузке изображений через `FileInputWidget` имеется возможность изменять порядок объектов драг-н-дропом, изменять
  размер и имя;
- при загрузке файлов драг-н-дропом порядок файлов сохраняется тем же, что и был в момент выделения файлов на
  компьютере (это очень удобно, если необходимо добавить к модели, к примеру, 50 изображений в строгом порядке);
- при загрузке изображений автоматическое определение горизонта по EXIF-метке;
- при необходимости добавления изображений к модели не через веб-интерфейс сайта, а при помощь консольных парсеров и
  других похожих случаев - такая возможность имеется. Для этого в системе предусмотрено два
  класса: `FileCreateFromInstance.php` и `FileCreateFromPath.php`.
- при работе с видео файлами - перекодировка их в h264 при помощи утилиты ffmpeg;

### Интернационализация

На данный этап модуль поддерживает следующие языки:

- Английский
- Русский

### Принцип работы

Информация о файлах хранится в таблице `file` и содержит связи с моделью через три поля:

- `class` - полное имя класса связанной модели
- `field` - имя поля модели
- `object_id` - primary key модели

При работе с виджетом добавления файлов, во время добавления файла в форму происходит его фоновая загрузка и обработка.
В результате этой обработки он записывается на диск и для него создается запись в таблице `file`, где поля `class`
и `field` заполнены данными из модели, а `object_id` присваивается только после сохранения модели ActiveRecord, к
которой подключено поведение. При удалении файла из формы он не удаляется с диска и из таблицы `file`, а просто его
object_id будет обращен в 0. Для периодической очистки такого рода бесхозных файлов можно периодически использоваться
консольную команду `files/console/clean`.

## Установка и настройка

Устанавливаем модуль через composer:
Выполняем команду

```bash
$ composer require floor12/yii2-module-files
```

или добавляем в секцию "required" файла composer.json

```json
"floor12/yii2-module-files": "dev-master"
```

Далее выполняем миграцию для создания таблицы `file`

```bash
$ ./yii migrate --migrationPath=@vendor/floor12/yii2-module-files/src/migrations/
```

Так как для работы модуля требуются контроллеры, прописываем модуль в конфигурации Yii2 приложения:

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

Параметры:

- `storage` - алиас пути к хранилищу файлов и исходников изображений на диске, по умолчанию располагается в папке
  storage в корне проекта;
- `cache` - алиас пути к хранилищу миниатюр изображений, которые модуль создает "на лету" по запросу и кеширует;
- `token_salt` - уникальная соль для безопасной работы виджета загрузки файлов.

## Использование

### Настройка модели ActiveRecord

Для добавление к модели ActiveRecord одного или нескольких полей с файлами, необходимо подключить к
ней `floor12\files\components\FileBehaviour'` и перечислить названия полей, которые будут хранить файлы. Например, для
модели User здесь будут определены 2 поля для хранения файлов: `avatar` и `documents`:

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

Чтобы отобразить красивые названия для полей, прописываем для них лейблы, как будто это обычные поля модели:

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

В методе `rules()` описываем правила валидации для наших файловых полей:

```php
public function rules()
{
    return [
        //Аватар является обязательным полем  
        ['avatar', 'required'],
        
        //В поле Аватар можно поместить 1 файл с разрешением 'jpg', 'png', 'jpeg', 'gif'
        ['avatar', 'file', 'extensions' => ['jpg', 'png', 'jpeg', 'gif'], 'maxFiles' => 1], 

        //А в поле documents можно помещать несколько документов в формате MS Word или Excel
        ['documents', 'file', 'extensions' => ['docx', 'xlsx'], 'maxFiles' => 10],
    ...    
```

### Обращение к файлам

Если `maxFiles`  в `FileValidator` будет равен единице, то поле модели будет хранить экземпляр
класса `floor12\files\models\File`. Например:

```php
// Поле href хранит в себе ссылку на исходник файла или картинки
echo Html::img($model->avatar->href)     

//Такая запись равнозначна, так как объект File при приведении к строке возвращает href
echo Html::img($model->avatar)          
```

Если файл является изображением, то можно запросить его миниатюру, передав в специальный метод ширину, высоту и флаг
конвертации в WEBP:

`File::getPreviewWebPath(int $width = 0, int $height = 0 ,bool $webp = false)`

Пример использования:

```php
//Запрашиваем миниатюру аватара пользователя шириной 200 пикселей
echo Html::img($model->avatar->getPreviewWebPath(200));     

//Запрашиваем миниатюру аватара пользователя шириной 200 пикселей и в формате WEBP
echo Html::img($model->avatar->getPreviewWebPath(200, true));     
             
```

В случае, если `maxFiles > 1`  и файлов можно загрузить несколько, то поле с файлами будет содержать не экземпляр
объекта  `floor12 \files\models\File`, а массив объектов:

```php
foreach ($model->docs as $doc}
    Html::a($doc->title, $doc->href);
```

Вот еще один пример продвинутого использования миниатюр. В данном варианте мы используем современные теги `picture`
и `source`, а так же медиа-запросы. В результате, у нас имеется 8 миниатюр, 4 в формате webp для тех браузерв, которые
поддерживают этот формат, а 4 в формате jpeg. Кроме того, устройствам с ретиной будут показывать изображения с двойным
разрешением. а обычным экранам - картинки обычного размера. Так же в примере используется разделение на разные
изображения при разной ширине экрана:

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

### Виджет для тега Picture

Если объект типа `File` является изображением (`$file->isImage() === true`), то для него можно
использовать `PictureWidget`. Этот виджет поможет в несколько строк кода сгенерировать тег picture с набором source и
srcset на базе заданных параметров. Например:

```php
echo PictureWidget::widget([
    'model' => $file,
    'alt' => 'Some alternative text',
    'width' => 100
]);
```

сгенерирует следующий html код:

```html

<picture>
    <source type="image/webp"
            srcset="/imageurl/image1xWebp 1x,/imageurl/image2xWebp 2x">
    <img src="/imageurl/image 1x"
         srcset="/imageurl/image 1x,/imageurl/image 2x"
         alt="<?= $model->title ?>">
</picture>
```

Дополнительный параметры можно посмотреть в исходном коде виджета.

### Виджет для списка файлов

В поставке модуля, есть виджет для вывода всех файлов, который дает возможность просматривать список файлов конкретного
поля, подключая для его отображения галерею [Lightbox2](https://lokeshdhakar.com/projects/lightbox2/) если в списке есть
картинки, осуществлять предпросмотр файлов MS Office, а так же имеется возможно скачать все приложенные к модели файлы
ZIP-архивом.

 ```php
echo \floor12\files\components\FileListWidget::widget([
    'files' => $model->docs, 
    'downloadAll' => true, 
    'zipTitle' => "Документы пользователя {$model->fullname}" 
]) 
```

В виджет необходимо передать массив объектов `File`, а так же можно задать дополнительные параметры:

- `title` - опционально задать заголовок блока (по-умолчанию берется из AttributeLabels)",
- `downloadAll` - показать кнопку "скачать все файлы",
- `zipTitle` - задать название файла для zip-архива,
- `passFirst` - пропустить при выводе первый файл (часто бывает необходимо вывести галерею без первой картинки,
  например, в новости, так как первая картинка "ушла" на обложку самой новости - как пример).

![FileInputWidget](https://floor12.net/files/default/get?hash=6482fa93391f5fdcbbf8eb8d242da684)

### Виджет для ActiveForm

Во время редактирования модели, необходимо использовать виджет `floor12\files\components\FileInputWidget`:

```php
    <?= $form->field($model, 'avatar')->widget(floor12\files\components\FileInputWidget::class) ?>
```

При этом, в зависимости того. установлен ли параметр `maxFiles` в `FileValidator`  равный единицы или
более, `FileInputWidget` примет необходимый вид, для загрузки одного файла или сразу нескольких. При необходимости можно
передать в виджет текст и для кнопки и класс для кнопки загрузки через параметры `uploadButtonText`
и `uploadButtonClass`.

## Участие в разработке

Буду рад любой помощи в разработке, поддержке и баг-репортах на этот модуль. 