### RouteCollection数据存储动态情况  
```php  

protected $routes = [
    GET=>[
        api/user=>route实例
        /=>route实例
        test=>route实例{
            validators
            macros
            uri=>test
            methods=>[GET,HEAD]
            action=>[
                middleware=>['web']
                uses=>App\Http\Controllers\Admin\TestController@index
                controller=>App\Http\Controllers\Admin\TestController@index
                namespace=>App\Http\Controllers
                prefix=>null
                where=>[]
            ]
            isFallback
            controller
            defaults
            wheres
            parameters
            parameterNames
            originalParameters
            computedMiddleware
            compiled
        }
    ],
    HEAD=>[
            api/user=>route实例【 action['uses']=>function (Request $request) {
                                  return $request->user();
                              }】
            /=>route实例【action['uses']=>function () {
                           return view('welcome');
                       }】
            test=>route实例
        ]
];

protected $allRoutes = [
    HEADapi/user=>route实例
    HEAD/=>route实例
    HEADtest=>route实例
];

protected $nameList = [];

protected $actionList = [
    App\Http\Controllers\Admin\TestController@index=>route实例
];

```