
# YII ActiveRecord Tools

Generates active record classes for YII2

This script generates model classes, and query classes for each table in the provided database.
The script generates base classes which contain all functional code, and concrete classes for you to modify.

Concrete classes are never overwritten.  
Base classes are overwritten when the script is ran.

## Installation

Install with composer

    composer require rauwebieten/yii-activerecord-tools
    
Add module to your console app config

```
    ...
    'modules' => [
        'activerecord_tools' => [
            'class' => \rauwebieten\yiiactiverecordtools\ActiveRecordToolsModule::class,
            'db' => 'db',
        ],
    ],
    'bootstrap' => ['activerecord_tools'],
    ...
```

possible configuration options:

- db: the name of the database component, defaults to 'db'
- namespace: the namespace in which the classes need to generate, defaults to 'app\models'
- baseModelClass: base class for generated active record models, defaults to ActiveRecord::class
- baseQueryClass: base class for generated qeury classes, defaults to ActiveQuery::class

## Usage

```
php yii activerecord_tools/generate/models
```

Models are generated in your models folder. Commit and use.