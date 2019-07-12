## CRUD整个流程注解     
- App\Http\Kernel类  

```php  
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}

```  

鸡类【基类】构造函数    
就是把一些没有什么用的废物【中间件类数组，全局，路由，优先】保存在路由里【干什么用啊】  
后面说，不急好吗  
```php  
 public function __construct(Application $app, Router $router)
    {
        $this->app = $app;
        $this->router = $router;

        /**
        protected $middlewarePriority = [
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Auth\Middleware\Authenticate::class,
                \Illuminate\Session\Middleware\AuthenticateSession::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Illuminate\Auth\Middleware\Authorize::class,
            ];
        **/
        $router->middlewarePriority = $this->middlewarePriority;

        /**
        protected $middlewareGroups = [
                'web' => [
                    \App\Http\Middleware\EncryptCookies::class,
                    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                    \Illuminate\Session\Middleware\StartSession::class,
                    // \Illuminate\Session\Middleware\AuthenticateSession::class,
                    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                    \App\Http\Middleware\VerifyCsrfToken::class,
                    \Illuminate\Routing\Middleware\SubstituteBindings::class,
                ],
        
                'api' => [
                    'throttle:60,1',
                    'bindings',
                ],
            ];

        **/
        foreach ($this->middlewareGroups as $key => $middleware) {
            $router->middlewareGroup($key, $middleware);
        }

        /**
        protected $routeMiddleware = [
                'auth' => \App\Http\Middleware\Authenticate::class,
                'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
                'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
                'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
                'can' => \Illuminate\Auth\Middleware\Authorize::class,
                'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
                'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
                'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
                'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            ];
        **/
        foreach ($this->routeMiddleware as $key => $middleware) {
            $router->aliasMiddleware($key, $middleware);
        }
    }
```  

- App\Http\Kernel->handle()方法  
    ```php  
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    ```  
    
    没错，这家伙调用了Request了，我们去看看它是怎么开车的，是怎么在秋名山是装逼的  
    
    ```php  
    呐，这吊毛长这样，它继续了Symfony的一个Request类     
    并且实现了数组访问式接口【ArrayAccess已经成为大量框架的菜了】     
    所以你以后再要获取请求的参数时，直接Request[请求的字段名]     
    它下面还扩展了【多继续trait】大量的方法    
    class Request extends SymfonyRequest implements Arrayable, ArrayAccess
    {
        use Concerns\InteractsWithContentTypes,
            Concerns\InteractsWithFlashData,
            Concerns\InteractsWithInput,
            Macroable;  
            
    鸡类  
    namespace Symfony\Component\HttpFoundation;
    
    use Symfony\Component\HttpFoundation\Exception\ConflictingHeadersException;
    use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
    use Symfony\Component\HttpFoundation\Session\SessionInterface;
    
    /**
     * Request represents an HTTP request.
     *
     * The methods dealing with URL accept / return a raw path (% encoded):
     *   * getBasePath
     *   * getBaseUrl
     *   * getPathInfo
     *   * getRequestUri
     *   * getUri
     *   * getUriForPath
     *
     * @author Fabien Potencier <fabien@symfony.com>
     */
    class Request{}
    你没有看错，是另一个老司机写的扩展包   
    
    ```  
    
    下面看它的具体方法  
    ```php  
     public static function capture()
        {
            static::enableHttpMethodParameterOverride();
    
            return static::createFromBase(SymfonyRequest::createFromGlobals());
        }
    ```  
    调用父类的东西  
    ```php  
    public static function enableHttpMethodParameterOverride()
        {
            self::$httpMethodParameterOverride = true;
        }
    ```  
    
    Symfony\Component\HttpFoundation->createFromGlobals()      
    ```php  
    public static function createFromGlobals()
        {
            $request = self::createRequestFromFactory($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);
    
            if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
                && \in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
            ) {
                parse_str($request->getContent(), $data);
                $request->request = new ParameterBag($data);
            }
    
            return $request;
        }
    ```   
    ```php  
    private static function createRequestFromFactory(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
        {
            if (self::$requestFactory) {
                $request = (self::$requestFactory)($query, $request, $attributes, $cookies, $files, $server, $content);
    
                if (!$request instanceof self) {
                    throw new \LogicException('The Request factory must return an instance of Symfony\Component\HttpFoundation\Request.');
                }
    
                return $request;
            }
    
            return new static($query, $request, $attributes, $cookies, $files, $server, $content);
        }
    ```  
    
    实例化Symfony\Component\HttpFoundation  
    ```php  
     public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
        {
            $this->initialize($query, $request, $attributes, $cookies, $files, $server, $content);
        }
    ```  
    
    ```php  
    //真是老母猪，一套套的，好玩吗？[^_^]老外就是喜欢这样      
    //$_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER
    public function initialize(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
        {
        //get
            $this->request = new ParameterBag($request);
            //post
            $this->query = new ParameterBag($query);
            //[]
            $this->attributes = new ParameterBag($attributes);
            //cookie
            $this->cookies = new ParameterBag($cookies);
            //files
            $this->files = new FileBag($files);
            //server
            $this->server = new ServerBag($server);
            //header
            $this->headers = new HeaderBag($this->server->getHeaders());
            //content
            $this->content = $content;
            $this->languages = null;
            $this->charsets = null;
            $this->encodings = null;
            $this->acceptableContentTypes = null;
            $this->pathInfo = null;
            $this->requestUri = null;
            $this->baseUrl = null;
            $this->basePath = null;
            $this->method = null;
            $this->format = null;
        }
    ```
    超级变量封装类  
    ```php  
    namespace Symfony\Component\HttpFoundation;
    
    /**
     * ParameterBag is a container for key/value pairs.
     *
     * @author Fabien Potencier <fabien@symfony.com>
     */
     //实现了count方法和迭代器动作  
    class ParameterBag implements \IteratorAggregate, \Countable
    {
    
        protected $parameters;
        //构造函数
        public function __construct(array $parameters = [])
        {
            $this->parameters = $parameters;
        }
        //返回数组
        public function all()
        {
            return $this->parameters;
        }
        //返回键
        public function keys()
        {
            return array_keys($this->parameters);
        }
        //数组替换
        public function replace(array $parameters = [])
        {
            $this->parameters = $parameters;
        }
        //添加【或是替换】
        public function add(array $parameters = [])
        {
            $this->parameters = array_replace($this->parameters, $parameters);
        }
    
        //获取指定索引的值
        public function get($key, $default = null)
        {
            return \array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
        }
        //添加
        public function set($key, $value)
        {
            $this->parameters[$key] = $value;
        }
    
        //判断某key是否存在
        public function has($key)
        {
            return \array_key_exists($key, $this->parameters);
        }
        //删除操作
        public function remove($key)
        {
            unset($this->parameters[$key]);
        }
        //匹配字母
        public function getAlpha($key, $default = '')
        {
            return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
        }
        //匹配字母和数字
        public function getAlnum($key, $default = '')
        {
            return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
        }
        //获取数字
        public function getDigits($key, $default = '')
        {
            // we need to remove - and + because they're allowed in the filter
            return str_replace(['-', '+'], '', $this->filter($key, $default, FILTER_SANITIZE_NUMBER_INT));
        }
        
        public function getInt($key, $default = 0)
        {
            return (int) $this->get($key, $default);
        }
        
        public function getBoolean($key, $default = false)
        {
            return $this->filter($key, $default, FILTER_VALIDATE_BOOLEAN);
        }
        //过滤操作https://www.php.net/manual/zh/function.filter-var.php
        public function filter($key, $default = null, $filter = FILTER_DEFAULT, $options = [])
        {
        //获取值
            $value = $this->get($key, $default);
    
            // Always turn $options into an array - this allows filter_var option shortcuts.
            if (!\is_array($options) && $options) {
                $options = ['flags' => $options];
            }
    
            // Add a convenience check for arrays.
            if (\is_array($value) && !isset($options['flags'])) {
                $options['flags'] = FILTER_REQUIRE_ARRAY;
            }
    
            return filter_var($value, $filter, $options);
        }
        //迭代器
        public function getIterator()
        {
            return new \ArrayIterator($this->parameters);
        }
        //统计
        public function count()
        {
            return \count($this->parameters);
        }
    }
    ```  
    其它封装包类大家自己去看看就懂了  
    
    具体来看handle方法  
    ```php  
    public function handle($request)
        {
            try {
                $request->enableHttpMethodParameterOverride();
                //来看看这家伙
                $response = $this->sendRequestThroughRouter($request);
            } catch (Exception $e) {
                $this->reportException($e);
                //运行异常的捕获
                $response = $this->renderException($request, $e);
            } catch (Throwable $e) {
                $this->reportException($e = new FatalThrowableError($e));
    
                $response = $this->renderException($request, $e);
            }
            //运行事件对应的监听器类
            $this->app['events']->dispatch(
                new Events\RequestHandled($request, $response)
            );
    
            return $response;
        }

    ```
    `$response = $this->sendRequestThroughRouter($request);` 这家伙引发的爆炸【连环】  
    所以内容会非常长，我们慢慢分析  
    ```php  
    protected function sendRequestThroughRouter($request)
        {
        //这个不用说了吧
        //所以你用的时候可以实现app()['request']就可以调用相应的请求对象  
            $this->app->instance('request', $request);
            //这个暂时不用管
            Facade::clearResolvedInstance('request');
            //马上分析这家伙，这家伙逼事也多
            $this->bootstrap();
    
            return (new Pipeline($this->app))
                        ->send($request)
                        ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
                        ->then($this->dispatchToRouter());
        }
    ```
    框架启动时【hTTP请求时】  
    ` $this->bootstrap();`  
    ```php  
    public function bootstrap()
        {
            if (! $this->app->hasBeenBootstrapped()) {
            //直接看这句
            /**
             protected $bootstrappers = [
                    \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
                    \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
                    \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
                    \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
                    \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
                    \Illuminate\Foundation\Bootstrap\BootProviders::class,
                ];
            **/
                $this->app->bootstrapWith($this->bootstrappers());
            }
        }
    ```  
    启动数组  
    ```php  
    protected function bootstrappers()
        {
            return $this->bootstrappers;
        }
    ```  
    Application->bootstrapWith()     
    ```php  
    public function bootstrapWith(array $bootstrappers)
        {
            $this->hasBeenBootstrapped = true;
    
            foreach ($bootstrappers as $bootstrapper) { 
            //事件调度器【暂时先不用管】后面再说
                $this['events']->dispatch('bootstrapping: '.$bootstrapper, [$this]);
            //make方法不用说了，就是实例化对象返回【前面开车说过了】
            //实例化启动类数组并执行
                $this->make($bootstrapper)->bootstrap($this);
    
                $this['events']->dispatch('bootstrapped: '.$bootstrapper, [$this]);
            }
        }
    ```  
    
    环境启动类` \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,`  
    ```php  
    public function bootstrap(Application $app)
        {
        //不用管
            if ($app->configurationIsCached()) {
                return;
            }
            //给应用Application设置环境配置文件路径 
            $this->checkForSpecificEnvironmentFile($app);
    
            try {
                $this->createDotenv($app)->safeLoad();
            } catch (InvalidFileException $e) {
                $this->writeErrorAndDie($e);
            }
        }
    ```  
    ```php  
    protected function checkForSpecificEnvironmentFile($app)
        {
        //运行console【就是你跑的php artisan xxx时才看这】  
            if ($app->runningInConsole() && ($input = new ArgvInput)->hasParameterOption('--env')) {
                if ($this->setEnvironmentFilePath(
                    $app, $app->environmentFile().'.'.$input->getParameterOption('--env')
                )) {
                    return;
                }
            }
    
            if (! env('APP_ENV')) {
                return;
            }
            //保存环境配置文件路径 
            $this->setEnvironmentFilePath(
                $app, $app->environmentFile().'.'.env('APP_ENV')
            );
        }
    ```  
    设置环境文件路径   
    ```php  
    protected function setEnvironmentFilePath($app, $file)
        {
            if (file_exists($app->environmentPath().'/'.$file)) {
                $app->loadEnvironmentFrom($file);
    
                return true;
            }
    
            return false;
        }
    ```  
    ```php  
    Application-> loadEnvironmentFrom($file)       
                         {      
                             $this->environmentFile = $file;      
                     
                             return $this;     
                         }   
    ```   
    
    ```php  
    protected function createDotenv($app)
        {
            return Dotenv::create(
                $app->environmentPath(),
                $app->environmentFile(),
                new DotenvFactory([new EnvConstAdapter, new ServerConstAdapter, new PutenvAdapter])
            );
        }
    ```  
    Dotenv包的使用  
    ```json  
    {
        "name": "vlucas/phpdotenv",
        "description": "Loads environment variables from `.env` to `getenv()`, `$_ENV` and `$_SERVER` automagically.",
        "keywords": ["env", "dotenv", "environment"],
        "license" : "BSD-3-Clause",
        "authors" : [
            {
                "name": "Vance Lucas",
                "email": "vance@vancelucas.com",
                "homepage": "http://www.vancelucas.com"
            }
        ],
        "require": {
            "php": "^5.4 || ^7.0",
            "phpoption/phpoption": "^1.5",
            "symfony/polyfill-ctype": "^1.9"
        },
        "require-dev": {
            "phpunit/phpunit": "^4.8.35 || ^5.0 || ^6.0"
        },
        "autoload": {
            "psr-4": {
                "Dotenv\\": "src/"
            }
        },
        "extra": {
            "branch-alias": {
                "dev-master": "3.4-dev"
            }
        }
    }

    ```  
    包的使用手册地址  
    [dontenv](https://packagist.org/packages/vlucas/phpdotenv)     
    ```doc 
    Add your application configuration to a .env file in the root of your project. Make sure the .env file is added to your .gitignore so it is not checked-in the code
    
    S3_BUCKET="dotenv"
    SECRET_KEY="souper_seekret_key"
    Now create a file named .env.example and check this into the project. This should have the ENV variables you need to have set, but the values should either be blank or filled with dummy data. The idea is to let people know what variables are required, but not give them the sensitive production values.
    
    S3_BUCKET="devbucket"
    SECRET_KEY="abc123"
    You can then load .env in your application with:
    
    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();
    Optionally you can pass in a filename as the second parameter, if you would like to use something other than .env
    
    $dotenv = Dotenv\Dotenv::create(__DIR__, 'myconfig');
    $dotenv->load();
    All of the defined variables are now accessible with the getenv method, and are available in the $_ENV and $_SERVER super-globals.
    
    $s3_bucket = getenv('S3_BUCKET');
    $s3_bucket = $_ENV['S3_BUCKET'];
    $s3_bucket = $_SERVER['S3_BUCKET'];
    You should also be able to access them using your framework's Request class (if you are using a framework).
    
    $s3_bucket = $request->env('S3_BUCKET');
    $s3_bucket = $request->getEnv('S3_BUCKET');
    $s3_bucket = $request->server->get('S3_BUCKET');
    $s3_bucket = env('S3_BUCKET');
    ```   
    
    具体我就不再细说这个包是怎么加载的，我们主要看laravel，大家玩玩就会   
    
    框架配置文件的加载`\Illuminate\Foundation\Bootstrap\LoadConfiguration::class,`  
    
    ```php  
    public function bootstrap(Application $app)
        {
            $items = [];
            //缓存配置文件是否存在
            if (file_exists($cached = $app->getCachedConfigPath())) {
                $items = require $cached;
    
                $loadedFromCache = true;
            }
            //这个不用说了吧
            $app->instance('config', $config = new Repository($items));
    
            if (! isset($loadedFromCache)) {
                $this->loadConfigurationFiles($app, $config);
            }

            $app->detectEnvironment(function () use ($config) {
                return $config->get('app.env', 'production');
            });
    
            date_default_timezone_set($config->get('app.timezone', 'UTC'));
    
            mb_internal_encoding('UTF-8');
        }
    ```  
    
    配置类结构  
    ```php  
    namespace Illuminate\Config;
    
    use ArrayAccess;
    use Illuminate\Support\Arr;
    use Illuminate\Contracts\Config\Repository as ConfigContract;
    你没有看错，这配置也实现了数组式访问接口
    所以你应该知道怎么使用config了
    class Repository implements ArrayAccess, ConfigContract{}
    ```  
    
    异常和错误的注册` \Illuminate\Foundation\Bootstrap\HandleExceptions::class,`  
    ```php  
    public function bootstrap(Application $app)
        {
            $this->app = $app;
    
            error_reporting(-1);
            //错误注册
            set_error_handler([$this, 'handleError']);
            //异常
            set_exception_handler([$this, 'handleException']);
            //运行结束后
            register_shutdown_function([$this, 'handleShutdown']);
    
            if (! $app->environment('testing')) {
                ini_set('display_errors', 'Off');
            }
        }
    ```  
    
    注册伪装类【门面】` \Illuminate\Foundation\Bootstrap\RegisterFacades::class,`  
    ```php  
    public function bootstrap(Application $app)
        {
            Facade::clearResolvedInstances();
            //我喜欢叫伪装【不要跟我刚^_^】 
            //没错，就是把Application存在伪装类的鸡类里
            Facade::setFacadeApplication($app);
    
            AliasLoader::getInstance(array_merge(
            //呐，那个配置类的使用
            //得到配置文件的类别名数组【具体是啥自己去打开瞧瞧】
                $app->make('config')->get('app.aliases', []),
                //这个前面说过了吧，就是第三方扩展包【为laravel写的】
                //不是为laravel写的就没有
                $app->make(PackageManifest::class)->aliases()
            ))->register();
        }
    ```  
    伪装类注册  
    ```php  
     public function register()
        {
            if (! $this->registered) {
                $this->prependToLoaderStack();
    
                $this->registered = true;
            }
        }
    ```  
    ```php  
    //这个本人在laravel-china社区说过它 
    //涉及的队列自己去看
    protected function prependToLoaderStack()
        {
            spl_autoload_register([$this, 'load'], true, true);
        }
    ```  
    
    服务提供类加载` \Illuminate\Foundation\Bootstrap\RegisterProviders::class,`  
    这家伙流程比较多，耐心看吧【当片来看】  
    ```php  
    public function bootstrap(Application $app)
        {
            $app->registerConfiguredProviders();
        }
    ```  
    ```php  
    public function registerConfiguredProviders()
        {
        //$this->config['app.providers'] 得到配置文件的服务提供类数组
            $providers = Collection::make($this->config['app.providers'])
                            ->partition(function ($provider) {//数组分App和Illuminate类服务提供类数组
                            //判断$provider类名前面是否是Illuminate开头的
                                return Str::startsWith($provider, 'Illuminate\\');
                            });
            //配置文件的服务提供类与第三方服务提供类合并【第三方主要是专门为laravel开发扩展包的老司机们】  
            $providers->splice(1, 0, [$this->make(PackageManifest::class)->providers()]);
            //Application,文件对象，bootstarp/cache/services.php文件
            //$providers->collapse() 将多维数组转换为一维【具体我不看了，LTS 5.5版本分析过】 
            //->toArray() 搞成数组
            (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
                        ->load($providers->collapse()->toArray());
        }
    ```  
    Collection->make([])  
    ```php  
    public static function make($items = [])
        {
            return new static($items);
        }
    ```  
    ```php  
    public function __construct($items = [])
        {
        //就直接返回数组保存
            $this->items = $this->getArrayableItems($items);
        }
    ```  
    ```php  
    protected function getArrayableItems($items)
        {
            if (is_array($items)) {
                return $items;
            } elseif ($items instanceof self) {
                return $items->all();
            } elseif ($items instanceof Arrayable) {
                return $items->toArray();
            } elseif ($items instanceof Jsonable) {
                return json_decode($items->toJson(), true);
            } elseif ($items instanceof JsonSerializable) {
                return $items->jsonSerialize();
            } elseif ($items instanceof Traversable) {
                return iterator_to_array($items);
            }
    
            return (array) $items;
        }
    ```  
    ```php  
    Collection->partition($key, $operator = null, $value = null)
                    {
                        $partitions = [new static, new static];
                
                        $callback = func_num_args() === 1
                                ? $this->valueRetriever($key)//判断是否是匿名函数
                                : $this->operatorForWhere(...func_get_args());
                
                        foreach ($this->items as $key => $item) {
                        //这里主要是区分服务提供类分别是Illuminate和App开头的【你最好看去看config/app.php的服务提供类数组,ok?】
                        //$partitions[0][]=Illuminate\\XXX
                        //$partitions[1][]=App\\XXX
                            $partitions[(int) ! $callback($item, $key)][$key] = $item;
                        }
                        //分好后在封装返回【真是套路多】  
                        return new static($partitions);
                    }
    ```  
    
    Collection->valueRetriever()匿名函数判断   
    ```php  
      protected function valueRetriever($value)
        {
        /**
        protected function useAsCallable($value)
            {
                return ! is_string($value) && is_callable($value);
            }
        **/
            if ($this->useAsCallable($value)) {
                return $value;
            }
    
            return function ($item) use ($value) {
                return data_get($item, $value);
            };
        }
    ```
    Str->startWith()判断数据是否含有指定的前缀字符
    ```php  
    public static function startsWith($haystack, $needles)
        {
            foreach ((array) $needles as $needle) {
                if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                    return true;
                }
            }
    
            return false;
        }
    ```  
    服务提供类合并  
    ```php  
     public function splice($offset, $length = null, $replacement = [])
        {
            if (func_num_args() === 1) {
                return new static(array_splice($this->items, $offset));
            }
            //https://www.php.net/manual/zh/function.array-splice.php  
            //怎么合并的，看看手册就懂了【ok】
            return new static(array_splice($this->items, $offset, $length, $replacement));
        }
    ```
    服务仓库类  
    ```php  
    class ProviderRepository
    {
        /**
         * The application implementation.
         *
         * @var \Illuminate\Contracts\Foundation\Application
         */
        protected $app;
    
        /**
         * The filesystem instance.
         *
         * @var \Illuminate\Filesystem\Filesystem
         */
        protected $files;
    
        /**
         * The path to the manifest file.
         *
         * @var string
         */
        protected $manifestPath;
    
        /**
         * Create a new service repository instance.
         *
         * @param  \Illuminate\Contracts\Foundation\Application  $app
         * @param  \Illuminate\Filesystem\Filesystem  $files
         * @param  string  $manifestPath
         * @return void
         */
        public function __construct(ApplicationContract $app, Filesystem $files, $manifestPath)
        {
            $this->app = $app;
            $this->files = $files;
            $this->manifestPath = $manifestPath;
        }
    ```
    服务仓库类加载服务提供类【数组】  
    Illuminate\Foundation\ProviderRepository->load()       
    0、服务提供类合并【config/app.php+bootstrap/cache/packages.php的服务提供类】 
    1、服务提供类更新【检测是否安装了新的扩展包--针对laravel的扩展包】  
    2、给服务提供类注册事件【事件调度时激活运行】  
    3、eager类的服务提供类则直接运行其register方法  
    4、延时的服务提供类则保存 
    ```php  
    public function load(array $providers)
        {
        //加载bootstrap/cache/services.php数组构成
        //['when',服务提供类数组]
            $manifest = $this->loadManifest();
            //是否要更新
            if ($this->shouldRecompile($manifest, $providers)) {
            //服务提供类分类【deferred,when,eager】并保存在bootstrap/cache/services.php
                $manifest = $this->compileManifest($providers);
            }
            //给服务提供类注册事件监听器
            foreach ($manifest['when'] as $provider => $events) {
                $this->registerLoadEvents($provider, $events);
            }
            //运行服务提供类的注册方法
            foreach ($manifest['eager'] as $provider) {
            //前面说过了哦
                $this->app->register($provider);
            }
            //延时加载的服务提供类
            /***
            public function addDeferredServices(array $services)
                {
                    $this->deferredServices = array_merge($this->deferredServices, $services);
                }
            **/
            $this->app->addDeferredServices($manifest['deferred']);
        }

    ```  
    加载第三方服务提供类 
    ```php  
    public function loadManifest()
        {
            if ($this->files->exists($this->manifestPath)) {
            //返回bootstrap/cache/services.php里的服务提供类数组
                $manifest = $this->files->getRequire($this->manifestPath);
    
                if ($manifest) {
                    return array_merge(['when' => []], $manifest);
                }
            }
        }
    ```  
    
    检测是否要重新生成服务提供类文件  
    ```php  
    public function shouldRecompile($manifest, $providers)
        {
        //如果bootstrap/cache/serives.php不存在或是安装了新的第三方服务扩展民
            return is_null($manifest) || $manifest['providers'] != $providers;
        }
    ```   
    服务提供类分类合并并保存在bootstrap/cache/services.php  
    分deferred,when,eager
    ```php  
     protected function compileManifest($providers)
        {
           //return ['providers' => $providers, 'eager' => [], 'deferred' => []];
            $manifest = $this->freshManifest($providers);
            //循环服务提供类数组
            foreach ($providers as $provider) {
                $instance = $this->createProvider($provider);
                //服务提供类的成员deferred=true时
                if ($instance->isDeferred()) {
                //服务提供类的providers方法 
                //比如你去看看ConsoleSupportServiceProvider这吊毛，它就是这吊样  
                /**
                 public function provides()
                    {
                        $provides = [];
                
                        foreach ($this->providers as $provider) {
                        //实例化服务提供类
                            $instance = $this->app->resolveProvider($provider);
                            //合并服务提供类
                            $provides = array_merge($provides, $instance->provides());
                        }
                
                        return $provides;
                    }
                **/
                //我们再来看
                    foreach ($instance->provides() as $service) {
                    //存储某个服务提供类下的服务提供类数组
                        $manifest['deferred'][$service] = $provider;
                    }
    
                    $manifest['when'][$provider] = $instance->when();
                }
    
                else {
                //及时要运行的服务提供类
                    $manifest['eager'][] = $provider;
                }
            }
            //写入bootstrap/cache/servies.php文件
            return $this->writeManifest($manifest);
        }
    ```
    服务提供类保存  
    ```php  
    public function writeManifest($manifest)
        {
            if (! is_writable(dirname($this->manifestPath))) {
                throw new Exception('The bootstrap/cache directory must be present and writable.');
            }
    
            $this->files->replace(
                $this->manifestPath, '<?php return '.var_export($manifest, true).';'
            );
    
            return array_merge(['when' => []], $manifest);
        }
    ```  
    
    给服务提供类注册一个事件【事件后面再说，先看片】  
    ```php  
    protected function registerLoadEvents($provider, array $events)
        {
            if (count($events) < 1) {
                return;
            }
            //以key,value形式保存在事件数组里，value为监听器
            $this->app->make('events')->listen($events, function () use ($provider) {
                $this->app->register($provider);
            });
        }
    ```  
    
    运行服务提供类的boot方法`\Illuminate\Foundation\Bootstrap\BootProviders::class,`  
    
    ```php  
     public function bootstrap(Application $app)
        {
            $app->boot();
        }
    ```  
    
    Application->boot()   
    ```php  
    public function boot()
        {
            if ($this->booted) {
                return;
            }
    
    //运行启动回调
            $this->fireAppCallbacks($this->bootingCallbacks);
    
            array_walk($this->serviceProviders, function ($p) {
            //运行前面跑过的服务提供类【就是eager跑时，会保存在Application->serviceProviders里啦】  
                $this->bootProvider($p);
            });
    
            $this->booted = true;
    
            $this->fireAppCallbacks($this->bootedCallbacks);
        }
    ```  
    ```php  
    protected function fireAppCallbacks(array $callbacks)
        {
            foreach ($callbacks as $callback) {
                call_user_func($callback, $this);
            }
        }
    ```  
    
    ```php  
    protected function bootProvider(ServiceProvider $provider)
        {
            if (method_exists($provider, 'boot')) {
                return $this->call([$provider, 'boot']);
            }
        }
    ```  
    
- 分析`(new Pipeline($this->app))`   
    ```php  
     return (new Pipeline($this->app))
                        ->send($request)
                        ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
                        ->then($this->dispatchToRouter());
    ```
   Pipeline类  
   ```php   
   namespace Illuminate\Routing;
   
   use Closure;
   use Exception;
   use Throwable;
   use Illuminate\Http\Request;
   use Illuminate\Contracts\Debug\ExceptionHandler;
   use Illuminate\Pipeline\Pipeline as BasePipeline;
   use Symfony\Component\Debug\Exception\FatalThrowableError;
   
   /**
    * This extended pipeline catches any exceptions that occur during each slice.
    *
    * The exceptions are converted to HTTP responses for proper middleware handling.
    */
   class Pipeline extends BasePipeline{}
   鸡类构造函数 
   public function __construct(Container $container = null)
       {
           $this->container = $container;
       }
   ```  
   
   Pipeline->send()方法  
   ```php  
    public function send($passable)
       {
       //当前请求对象
           $this->passable = $passable;
   
           return $this;
       }
   ```  
   
   Pipeline->through()方法  
   ```php  
   through($this->app->shouldSkipMiddleware() ? [] : $this->middleware) 
   public function through($pipes)
       {
       //全局中间件数组【元素是类】  
           $this->pipes = is_array($pipes) ? $pipes : func_get_args();
   
           return $this;
       }
   ```  
   
   Pipeline->then()方法  
   ```php  
   then($this->dispatchToRouter())   
   //$this->dispatchToRouter()返回的是个匿名函数【还是双层封装的匿名函数】套路真多  
  public function then(Closure $destination)
      {
      //后面我们来分析这玩意【这玩意是玄机】  
          $pipeline = array_reduce(
          //中间件数组倒序
          /**
         $middleware = [
                  \App\Http\Middleware\CheckForMaintenanceMode::class,
                  \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
                  \App\Http\Middleware\TrimStrings::class,
                  \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
                  \App\Http\Middleware\TrustProxies::class,
              ];
          **/
          //所以先运行\App\Http\Middleware\TrustProxies::class它
              array_reverse($this->pipes), $this->carry(), $this->prepareDestination($destination)
          );
  
          return $pipeline($this->passable);
      }
   ```    
   
   路由调度`$this->dispatchToRouter()`   
   
   Kernel->dispatchToRouter()方法 
   ```php  
   protected function dispatchToRouter()
       {
           return function ($request) {
               $this->app->instance('request', $request);
   
               return $this->router->dispatch($request);
           };
       }
   ```  
   再次封装  
   ```php  
    protected function prepareDestination(Closure $destination)
       {
           return function ($passable) use ($destination) {
               return $destination($passable);
           };
       }
   ```
   
   
