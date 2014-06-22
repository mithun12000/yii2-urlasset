Yii2-UrlAsset
=============

Url Asset for Yii2 just like AssetBundle except it will work for creating menu for site module dynamically.


Installation
=======

Using ```composer```

```
"require": {
	...other dependency...	
	"mithun12000/yii2-urlasset":"*"
},
```

Add as extension. Code:

```php

'UrlAsset' => 
  [
    'name' => 'UrlAsset',
    'version' => '1.0',
    'alias' => 
    [
      '@yii/UrlAsset/' => [EXTENSION_PATH] '/UrlAsset',
    ],
  ],
  
```

Configure
=======

In Layout / view file.
```
use Yii\UrlAsset\component\UrlAsset;
$urls = new UrlAsset(['assetFile'=>'AppUrlAsset']); //default file for search is AppUrlAsset.php with namespace can be configurable. 
$urls->registerAll($this);
$urls->setParams($this);

//to get all menu item.
$menuitems = $this->params['urls'];
```
