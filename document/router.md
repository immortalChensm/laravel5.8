### Illuminate\Routing\Route路由类的数据动态存储情况【只是某时刻】   
```php  

public $uri=test;

public $methods=['GET', 'HEAD'];

public $action=[
                    middleware=[
                           web
                        ],
                    uses=App\Http\Controllers\Admin\TestController@index,
                    controller=App\Http\Controllers\Admin\TestController@index,
                    namespace=App\Http\Controllers,
                    where=[],
                   ];


public $isFallback = false;

public $controller;


public $defaults = [];

public $wheres = [];


public $parameters;


public $parameterNames;


protected $originalParameters;


public $computedMiddleware;


public $compiled;


protected $router;


protected $container;

public static $validators;

```   
