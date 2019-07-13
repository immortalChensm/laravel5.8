### CRUD整个流程注解  
本篇将写一个控制器输出数据库里的内容并展示在模板文件里，我们分析它的整个架构流程  

控制器里的内容  
```php  
<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    //
    function index()
    {
        $data = DB::table("test")->get();
        return view("admin.index",compact('data'));
    }
}

```  

模板内容  
```html 
hello,laravel5.8
{{$data}}
```  

路由文件内容  

```php  
<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', "Admin\TestController@index");

```  

运行结果  

![web](images/web.png)  

下面我们基于这样的结果分析整个它的整个流程【生命周期】  

- index.php入口文件起程   

    请求的uri地址【http://localhost:1234/test】  
    下面我们来看入口文件内容  
    
    ```php 
    <?php
    
    define('LARAVEL_START', microtime(true));
    //引入composer的自动加载处理文件
    require __DIR__.'/../vendor/autoload.php';
    //Application的实例
    $app = require_once __DIR__.'/../bootstrap/app.php';
  
    //运行Kernel
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    //处理http请求
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    //响应结束
    $response->send();
    
    $kernel->terminate($request, $response);

    ```  
    
    下面我们来看`$app = require_once __DIR__.'/../bootstrap/app.php';`它的内容  
    
    ```php  
    <?php

    $app = new Illuminate\Foundation\Application(
        $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
    );

    $app->singleton(
        Illuminate\Contracts\Http\Kernel::class,
        App\Http\Kernel::class
    );
    
    $app->singleton(
        Illuminate\Contracts\Console\Kernel::class,
        App\Console\Kernel::class
    );
    
    $app->singleton(
        Illuminate\Contracts\Debug\ExceptionHandler::class,
        App\Exceptions\Handler::class
    );
   
    
    return $app;

    ```

    看第一句吧  
    ```php  
    $app = new Illuminate\Foundation\Application(
            $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
        );
    ```  
    实例化Application【自己去想命名空间跟目录是怎么对应的】  
    
    下面来看这个类的部分内容  
    ```php  
    
    namespace Illuminate\Foundation;
    
    use Closure;
    use RuntimeException;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;
    use Illuminate\Http\Request;
    use Illuminate\Support\Collection;
    use Illuminate\Container\Container;
    use Illuminate\Filesystem\Filesystem;
    use Illuminate\Log\LogServiceProvider;
    use Illuminate\Support\ServiceProvider;
    use Illuminate\Events\EventServiceProvider;
    use Illuminate\Routing\RoutingServiceProvider;
    use Symfony\Component\HttpKernel\HttpKernelInterface;
    use Symfony\Component\HttpKernel\Exception\HttpException;
    use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
    use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
    use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    use Illuminate\Contracts\Foundation\Application as ApplicationContract;
    
    class Application extends Container implements ApplicationContract, HttpKernelInterface
    {
    
    }
    
    namespace Illuminate\Container;
    
    use Closure;
    use Exception;
    use ArrayAccess;
    use LogicException;
    use ReflectionClass;
    use ReflectionParameter;
    use Illuminate\Support\Arr;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Contracts\Container\Container as ContainerContract;
    
    class Container implements ArrayAccess, ContainerContract
    {
    
    }
    ```      
    
    它继承了Container【容器类】 
    Container实现了数组式接口【ArrayAccess】    
    
    既然它实例化了自然会运行构造函数【这个类怎么加载的，自己去看composer】  
    
    ```php  
    public function __construct($basePath = null)
        {
            if ($basePath) {
                $this->setBasePath($basePath);
            }
    
            $this->registerBaseBindings();
            $this->registerBaseServiceProviders();
            $this->registerCoreContainerAliases();
        }
    ```  
    看看`$this->setBasePath($basePath)`吧，看看它是做什么的  
    ```php  
    public function setBasePath($basePath)
        {
            $this->basePath = rtrim($basePath, '\/');
    
            $this->bindPathsInContainer();
    
            return $this;
        }
    ```  
    
    再继续看`$this->bindPathsInContainer();`  
    ```php  
    protected function bindPathsInContainer()
        {
        //app应用目录 
            $this->instance('path', $this->path());
            //根目录 
            $this->instance('path.base', $this->basePath());
            //resources/lang目录
            $this->instance('path.lang', $this->langPath());
            //config目录
            $this->instance('path.config', $this->configPath());
            //public目录
            $this->instance('path.public', $this->publicPath());
            //storage目录
            $this->instance('path.storage', $this->storagePath());
            //database目录
            $this->instance('path.database', $this->databasePath());
            //resources目录
            $this->instance('path.resources', $this->resourcePath());
            //bootstrap目录
            $this->instance('path.bootstrap', $this->bootstrapPath());
        }
    ```  
    
    再继续看instance方法呗  
    ```php  
    /**
    1、将$abstract,$instance以key,value形式保存在Application->instances[]数组里
    **/
    public function instance($abstract, $instance)
        {
        //不用管这句，它目前没有什么用处
            $this->removeAbstractAlias($abstract);
    
    //这个也不用管
            $isBound = $this->bound($abstract);
    //这也不用理
            unset($this->aliases[$abstract]);
    
            // We'll check to determine if this type has been bound before, and if it has
            // we will fire the rebound callbacks registered with the container and it
            // can be updated with consuming classes that have gotten resolved here.
            
            //对就这样，把路径保存在这个数组里
            $this->instances[$abstract] = $instance;
    
            if ($isBound) {
                $this->rebound($abstract);
            }
    
            return $instance;
        }
    ```  
    
    ```php  
    protected function removeAbstractAlias($searched)
        {
            if (! isset($this->aliases[$searched])) {
                return;
            }
    
            foreach ($this->abstractAliases as $abstract => $aliases) {
                foreach ($aliases as $index => $alias) {
                    if ($alias == $searched) {
                        unset($this->abstractAliases[$abstract][$index]);
                    }
                }
            }
        }
    ```
    
    ```php  
    public function bound($abstract)
        {
            return isset($this->bindings[$abstract]) ||
                   isset($this->instances[$abstract]) ||
                   $this->isAlias($abstract);
        }
    ```
    
    构造函数里的第一句代码就分析完成，下面继续看第二句代码  
    
    `$this->registerBaseBindings();`  
    
    ```php  
     protected function registerBaseBindings()
        {
        //方便下次调用，不用再实例化了
            static::setInstance($this);
    
    //这个不说用，就是保存下来
            $this->instance('app', $this);
    //这个也一样
            $this->instance(Container::class, $this);
            $this->singleton(Mix::class);
    
            $this->instance(PackageManifest::class, new PackageManifest(
                new Filesystem, $this->basePath(), $this->getCachedPackagesPath()
            ));
        }
    ``` 
    
    第一句 
    ```php  
    public static function setInstance(ContainerContract $container = null)
        {
            return static::$instance = $container;
        }
    ```
    
    我们来看最后一句 
    ```php  
    $this->instance(PackageManifest::class, new PackageManifest(
                new Filesystem, $this->basePath(), $this->getCachedPackagesPath()
            ));
    ```
    
    我们来看一这个类的构造函数PackageManifest  
    ```php  
    public function __construct(Filesystem $files, $basePath, $manifestPath)
        {
            $this->files = $files;//文件对象
            $this->basePath = $basePath;//框架路径
            $this->manifestPath = $manifestPath;//bootstarp/cache/packages.php文件
            $this->vendorPath = $basePath.'/vendor';
        }
    ```   
    所以结果呢 
    ```php  
    $this->instances['app']=$this    
    $this->instances[Container::class]=$this
    $this->instances[PackageManifest::class]=new PackageManifest
    $this->instances[框架的各目录路径]=path
    
    ```
    现在来看这句`$this->singleton(Mix::class);`,5.5LTS版本没有这句哈【现在它增加了】  
    
    ```php  
    /**
    1、以key,value形式保存在Application->bindings[]里
    **/
    public function singleton($abstract, $concrete = null)
        {
            $this->bind($abstract, $concrete, true);
        }
    ```    
    
    继续看bind方法 
    ```php  
    public function bind($abstract, $concrete = null, $shared = false)
        {
            //目前没有什么用处
            $this->dropStaleInstances($abstract);

    
            if (is_null($concrete)) {
                $concrete = $abstract;
            }
            //这句暂时也没有什么用处
            if (! $concrete instanceof Closure) {
                $concrete = $this->getClosure($abstract, $concrete);
            }
    
            //没错，就保存在这里，还是以数组的方式保存的
            //$this->bindings[concrete]=Mix::class
            $this->bindings[$abstract] = compact('concrete', 'shared');

            //暂时也没有什么用处
            if ($this->resolved($abstract)) {
                $this->rebound($abstract);
            }
        }
    ```    
    
    先看第一句`$this->dropStaleInstances($abstract);`     
    
    ```php  
    protected function dropStaleInstances($abstract)
        {
            unset($this->instances[$abstract], $this->aliases[$abstract]);
        }
    ```    
    
    至此我们知道Application->instance(),Application->singleton()两方法的功能了  
    就是保存数据在一个数组里instances,bindings里【注册】   
    Application->singleton()依赖于Application->bind()方法【后面遇到就不在重复说了】  
    
    接着看构造函数的下一句 
    `$this->registerBaseServiceProviders();`     
    
    ```php  
    protected function registerBaseServiceProviders()
        {
            $this->register(new EventServiceProvider($this));
            $this->register(new LogServiceProvider($this));
            $this->register(new RoutingServiceProvider($this));
        }
    ```
    
    分析`$this->register` 
    ```php   
    
    /**
    1、先检测服务提供类是否运行过了，运行过的会保存在serviceProviders数组里
    2、运行服务提供类的register方法
    3、加载服务提供类的bindings[],singletons[]并将他们保存在Application->bindings[]
    4、保存服务提供者在serviceProviders里
    5、运行服务提供的boot方法【有判断】
    **/
    public function register($provider, $force = false)
        {
        //功能是检测$provider是否已经保存在$this->serviceProviders数组里了
            if (($registered = $this->getProvider($provider)) && ! $force) {
                return $registered;
            }
            //是字符的处理【现在不用管】
            if (is_string($provider)) {
            /**
             public function resolveProvider($provider)
                {
                    return new $provider($this);
                }
            **/
                $provider = $this->resolveProvider($provider);
            }
            //现在直接运行它的注册方法了【LTS5.5版本的套路可不是这样的】
            $provider->register();
    
            if (property_exists($provider, 'bindings')) {
                foreach ($provider->bindings as $key => $value) {
                //这个不用说了吧，前面讲过了
                //就是保存在Application->bindings[]里
                    $this->bind($key, $value);
                }
            }
    
            if (property_exists($provider, 'singletons')) {
                foreach ($provider->singletons as $key => $value) {
                //这个也是一样，感觉和直接bind没什么区别
                //估计作者又要瞎更新的，真的，相信我
                    $this->singleton($key, $value);
                }
            }
    
    /***
     protected function markAsRegistered($provider)
        {
            $this->serviceProviders[] = $provider;
    
            $this->loadedProviders[get_class($provider)] = true;
        }
    **/
    //记住已经加载的服务提供类
            $this->markAsRegistered($provider);
    
    //这个暂时先不管，因为它现在为false
            if ($this->booted) {
            /**
            protected function bootProvider(ServiceProvider $provider)
                {
                    if (method_exists($provider, 'boot')) {
                        return $this->call([$provider, 'boot']);
                    }
                }
            **/
                $this->bootProvider($provider);
            }
    
            return $provider;
        }
    ```  
    
    ```php  
    public function getProvider($provider)
        {
            return array_values($this->getProviders($provider))[0] ?? null;
        }
    
        /**
         * Get the registered service provider instances if any exist.
         *
         * @param  \Illuminate\Support\ServiceProvider|string  $provider
         * @return array
         */
        public function getProviders($provider)
        {
            $name = is_string($provider) ? $provider : get_class($provider);
    
    //从serviceProviders数组里循环，判断当前$provider是否属于$name指定的类
            return Arr::where($this->serviceProviders, function ($value) use ($name) {
                return $value instanceof $name;
            });
        }
    ```  
    看看这个Arr这个类的where方法吧  
    Arr的where方法  
    ```php  
      public static function where($array, callable $callback)
        {
        //https://www.php.net/manual/zh/function.array-filter.php 
        //
            return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
        }
    ```
     
    事件服务提供类的注册  
    ```php  
    namespace Illuminate\Events;
    
    use Illuminate\Support\ServiceProvider;
    use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
    
    class EventServiceProvider extends ServiceProvider
    {
        /**
         * Register the service provider.
         *
         * @return void
         */
        public function register()
        {
        //这个不用说了吧，将它们【events,function(Application){xxx}】
        //以key,value形式保存在Application->bindings[]数组里  
        //要用到的时候你就Applcation[events]  
        //为什么能这样开车啊？不知道【麻烦你看前面我怎么开车的】
            $this->app->singleton('events', function ($app) {
                return (new Dispatcher($app))->setQueueResolver(function () use ($app) {
                    return $app->make(QueueFactoryContract::class);
                });
            });
        }
    }  
    
    基类【鸡类】  
    abstract class ServiceProvider
    {
        /**
         * The application instance.
         *
         * @var \Illuminate\Contracts\Foundation\Application
         */
        protected $app;
    
        /**
         * Indicates if loading of the provider is deferred.
         *
         * @deprecated Implement the \Illuminate\Contracts\Support\DeferrableProvider interface instead. Will be removed in Laravel 5.9.
         *
         * @var bool
         */
        protected $defer = false;
    
        /**
         * The paths that should be published.
         *
         * @var array
         */
        public static $publishes = [];
    
        /**
         * The paths that should be published by group.
         *
         * @var array
         */
        public static $publishGroups = [];
    
        /**
         * Create a new service provider instance.
         *
         * @param  \Illuminate\Contracts\Foundation\Application  $app
         * @return void
         */
        public function __construct($app)
        {
            $this->app = $app;
        }
    ```  
    
    日志服务提供类的注册  
    ```php  
    namespace Illuminate\Log;
    
    use Illuminate\Support\ServiceProvider;
    
    class LogServiceProvider extends ServiceProvider
    {
        /**
         * Register the service provider.
         *
         * @return void
         */
        public function register()
        {
            $this->app->singleton('log', function () {
                return new LogManager($this->app);
            });
        }
    }
    ```  
    
    路由服务类的注册  
    ```php    
    
    后面用时我们再来看，记住它现在保存起来了
    <?php
    
    namespace Illuminate\Routing;
    
    use Illuminate\Support\ServiceProvider;
    use Psr\Http\Message\ResponseInterface;
    use Zend\Diactoros\Response as PsrResponse;
    use Psr\Http\Message\ServerRequestInterface;
    use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
    use Illuminate\Contracts\View\Factory as ViewFactoryContract;
    use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;
    use Illuminate\Routing\Contracts\ControllerDispatcher as ControllerDispatcherContract;
    
    class RoutingServiceProvider extends ServiceProvider
    {
        /**
         * Register the service provider.
         *
         * @return void
         */
        public function register()
        {
            //Application->bindings[router]=function(){xxx}
            $this->registerRouter();
            
            //Application->bindings[url]=function(){xxx}
            $this->registerUrlGenerator();
            
            //Application->bindings[redirect]=function(){xxx}
            $this->registerRedirector();
            
            //Application->bindings[ServerRequestInterface::class]=function(){xxx}
            $this->registerPsrRequest();
            
            //Application->bindings[ResponseInterface::class]=function(){xxx}
            $this->registerPsrResponse();
            
            //Application->bindings[ResponseFactoryContract::class]=function(){xxx}
            $this->registerResponseFactory();
            
            //Application->bindings[ControllerDispatcherContract::class]=function(){xxx}
            $this->registerControllerDispatcher();
            
        }
    ```  
    
    `$this->registerCoreContainerAliases();`  
    
    ```php  
    public function registerCoreContainerAliases()
        {
            foreach ([
                'app'                  => [self::class, \Illuminate\Contracts\Container\Container::class, \Illuminate\Contracts\Foundation\Application::class,  \Psr\Container\ContainerInterface::class],
                'auth'                 => [\Illuminate\Auth\AuthManager::class, \Illuminate\Contracts\Auth\Factory::class],
                'auth.driver'          => [\Illuminate\Contracts\Auth\Guard::class],
                'blade.compiler'       => [\Illuminate\View\Compilers\BladeCompiler::class],
                'cache'                => [\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class],
                'cache.store'          => [\Illuminate\Cache\Repository::class, \Illuminate\Contracts\Cache\Repository::class],
                'config'               => [\Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
                'cookie'               => [\Illuminate\Cookie\CookieJar::class, \Illuminate\Contracts\Cookie\Factory::class, \Illuminate\Contracts\Cookie\QueueingFactory::class],
                'encrypter'            => [\Illuminate\Encryption\Encrypter::class, \Illuminate\Contracts\Encryption\Encrypter::class],
                'db'                   => [\Illuminate\Database\DatabaseManager::class],
                'db.connection'        => [\Illuminate\Database\Connection::class, \Illuminate\Database\ConnectionInterface::class],
                'events'               => [\Illuminate\Events\Dispatcher::class, \Illuminate\Contracts\Events\Dispatcher::class],
                'files'                => [\Illuminate\Filesystem\Filesystem::class],
                'filesystem'           => [\Illuminate\Filesystem\FilesystemManager::class, \Illuminate\Contracts\Filesystem\Factory::class],
                'filesystem.disk'      => [\Illuminate\Contracts\Filesystem\Filesystem::class],
                'filesystem.cloud'     => [\Illuminate\Contracts\Filesystem\Cloud::class],
                'hash'                 => [\Illuminate\Hashing\HashManager::class],
                'hash.driver'          => [\Illuminate\Contracts\Hashing\Hasher::class],
                'translator'           => [\Illuminate\Translation\Translator::class, \Illuminate\Contracts\Translation\Translator::class],
                'log'                  => [\Illuminate\Log\LogManager::class, \Psr\Log\LoggerInterface::class],
                'mailer'               => [\Illuminate\Mail\Mailer::class, \Illuminate\Contracts\Mail\Mailer::class, \Illuminate\Contracts\Mail\MailQueue::class],
                'auth.password'        => [\Illuminate\Auth\Passwords\PasswordBrokerManager::class, \Illuminate\Contracts\Auth\PasswordBrokerFactory::class],
                'auth.password.broker' => [\Illuminate\Auth\Passwords\PasswordBroker::class, \Illuminate\Contracts\Auth\PasswordBroker::class],
                'queue'                => [\Illuminate\Queue\QueueManager::class, \Illuminate\Contracts\Queue\Factory::class, \Illuminate\Contracts\Queue\Monitor::class],
                'queue.connection'     => [\Illuminate\Contracts\Queue\Queue::class],
                'queue.failer'         => [\Illuminate\Queue\Failed\FailedJobProviderInterface::class],
                'redirect'             => [\Illuminate\Routing\Redirector::class],
                'redis'                => [\Illuminate\Redis\RedisManager::class, \Illuminate\Contracts\Redis\Factory::class],
                'request'              => [\Illuminate\Http\Request::class, \Symfony\Component\HttpFoundation\Request::class],
                'router'               => [\Illuminate\Routing\Router::class, \Illuminate\Contracts\Routing\Registrar::class, \Illuminate\Contracts\Routing\BindingRegistrar::class],
                'session'              => [\Illuminate\Session\SessionManager::class],
                'session.store'        => [\Illuminate\Session\Store::class, \Illuminate\Contracts\Session\Session::class],
                'url'                  => [\Illuminate\Routing\UrlGenerator::class, \Illuminate\Contracts\Routing\UrlGenerator::class],
                'validator'            => [\Illuminate\Validation\Factory::class, \Illuminate\Contracts\Validation\Factory::class],
                'view'                 => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
            ] as $key => $aliases) {
            //$key=别名
                foreach ($aliases as $alias) {
                //$alias 类名
                    $this->alias($key, $alias);
                }
            }
        }
    ```  
    
    类的别名存储  
    ```php  
    $abstract 简短别名
    $alias 类名
    public function alias($abstract, $alias)
        {
            if ($alias === $abstract) {
                throw new LogicException("[{$abstract}] is aliased to itself.");
            }
            //$this->aliases[类名] = 别名
            $this->aliases[$alias] = $abstract;
            //$this->abstractAliases[别名][] = 类名;
            $this->abstractAliases[$abstract][] = $alias;
        }
    ```
    Application实例化时做的事情    
    至此分析完成，实例化大体功能概括    
    1、保存框架的目录路径信息在Application->instances    
    2、自己存储本身【做单例】   
    3、存储自己Application在Application->instances    
    4、存储PackagesManifest类在Application->instances【干嘛用的，它专门负责第三方扩展包的信息存储处理】    
    5、将日志服务，路由服务，事件服务保存在Application->bindings[]数组里  
    6、将类的别名和类名分另存储在Application->aliases和Application->abstractAliases数组里  
    
    现在继续回到app.php文件看看  
    ```php   
    //不用我再说了吧，这么明晰的开车痕迹
    $app->singleton(
    //web
        Illuminate\Contracts\Http\Kernel::class,
        App\Http\Kernel::class
    );
    
    $app->singleton(
    //console
        Illuminate\Contracts\Console\Kernel::class,
        App\Console\Kernel::class
    );
    
    $app->singleton(
        Illuminate\Contracts\Debug\ExceptionHandler::class,
        App\Exceptions\Handler::class
    );
    
    return $app;

    ```  
    
    ok，现在我们再回到index.php文件里  
    
    `$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);`  
    
    现在分析Application->make()这骚货的动作   
    
    ```php  
     public function make($abstract, array $parameters = [])
        {
        //不用看了，这句没有用，告诉你
            $abstract = $this->getAlias($abstract);
    
    //protected $deferredServices = [];
    //isset都不通过，所以我们也别看了【开车要紧】
            if (isset($this->deferredServices[$abstract]) && ! isset($this->instances[$abstract])) {
                $this->loadDeferredProvider($abstract);
            }
    //对，去看鸡类【基类】  
            return parent::make($abstract, $parameters);
        }
    ```  
    
    ```php  
    //不用说了，我这兄弟什么都不懂
    //Illuminate\Contracts\Http\Kernel::class它是没有的，直接返回 
    
    public function getAlias($abstract)
        {
            if (! isset($this->aliases[$abstract])) {
                return $abstract;
            }
    
            return $this->getAlias($this->aliases[$abstract]);
        }
    ```  
    
    Container鸡类【基类】的make方法  
    ```php  
    public function make($abstract, array $parameters = [])
        {
            return $this->resolve($abstract, $parameters);
        }
    ```    
    
    Container鸡类【基类】resolve方法  
    ```php  
    protected function resolve($abstract, $parameters = [], $raiseEvents = true)
        {
        //这兄弟也真的，又来了，不用看
            $abstract = $this->getAlias($abstract);
    
    //这是个或逻辑，没有参数，也没有传输进来的Illuminate\Contracts\Http\Kernel::class  
    //所以它的值为1
            $needsContextualBuild = ! empty($parameters) || ! is_null( 
            /***
            protected function getContextualConcrete($abstract)
                {
                /**
                protected function findInContextualBindings($abstract)
                    {
                    //刚开始的时候这上下文数组是没有东西的
                    //包括构建的堆栈也是没有东西的
                        if (isset($this->contextual[end($this->buildStack)][$abstract])) {
                            return $this->contextual[end($this->buildStack)][$abstract];
                        }
                    }
                **/
                //很显然这句话没有什么用，就是故意整 你的
                    if (! is_null($binding = $this->findInContextualBindings($abstract))) {
                        return $binding;
                    }
                    //这里直接返回，因为根本不存在Illuminate\Contracts\Http\Kernel::class
                    if (empty($this->abstractAliases[$abstract])) {
                        return;
                    }
            
                    foreach ($this->abstractAliases[$abstract] as $alias) {
                        if (! is_null($binding = $this->findInContextualBindings($alias))) {
                            return $binding;
                        }
                    }
                }
            **/
                $this->getContextualConcrete($abstract)
            );
    
    //前面实例化的时候根本没有保存对应的实例，所以这里不管用
            if (isset($this->instances[$abstract]) && ! $needsContextualBuild) {
                return $this->instances[$abstract];
            }
    
    //存毛线参数，现在根本没有参数
            $this->with[] = $parameters;
    
    //目前返回App\Http\Kernel::class【因为我现在传递的是Illuminate\Contracts\Http\Kernel::class】 
    //注意，返回的是个被匿名函数包装的哦
    //具体原因在于Application->bind时做了如下判断
    /**
    if (! $concrete instanceof Closure) {
    //封装为匿名函数
                $concrete = $this->getClosure($abstract, $concrete);
            }
            
            或是返回一个具体的类
    ***/ 
            $concrete = $this->getConcrete($abstract);
    
    //
            if ($this->isBuildable($concrete, $abstract)) {
            //直接运行
                $object = $this->build($concrete);
            } else {
                $object = $this->make($concrete);
            }
    
    //这个不用看，目前没有什么用
            foreach ($this->getExtenders($abstract) as $extender) {
                $object = $extender($object, $this);
            }
    
    //这也没有什么用，不用管
            if ($this->isShared($abstract) && ! $needsContextualBuild) {
                $this->instances[$abstract] = $object;
            }
    
    //它为false不用管
            if ($raiseEvents) {
                $this->fireResolvingCallbacks($abstract, $object);
            }
            //已经处理的则保存
            $this->resolved[$abstract] = true;
    
            array_pop($this->with);
    
            return $object;
        }

    ```    
    ```php  
    /**
    就是检测从上下文数组里检测是否存在指定的$abstract
    **/
    protected function findInContextualBindings($abstract)
        {
            if (isset($this->contextual[end($this->buildStack)][$abstract])) {
                return $this->contextual[end($this->buildStack)][$abstract];
            }
        }
    ```  
    
    获取匿名函数【或是返回具体的类】
    ```php  
    protected function getConcrete($abstract)
        {
        //显然没有目前
            if (! is_null($concrete = $this->getContextualConcrete($abstract))) {
                return $concrete;
            }
    
    //唉这个有了，所以在这里返回Illuminate\Contracts\Http\Kernel::class
    //对应的App\Http\Kernel::class
            if (isset($this->bindings[$abstract])) {
                return $this->bindings[$abstract]['concrete'];
            }
    
            return $abstract;
        }
    
    ```  
    获取上下文匿名函数
    ```php  
     protected function getContextualConcrete($abstract)
        {
            //从上下文数组【堆栈】里查找
            if (! is_null($binding = $this->findInContextualBindings($abstract))) {
                return $binding;
            }
            //从别名【】=类名数组里查找
            if (empty($this->abstractAliases[$abstract])) {
                return;
            }
            
            //循环从抽像类别名数组里查找
            foreach ($this->abstractAliases[$abstract] as $alias) {
                if (! is_null($binding = $this->findInContextualBindings($alias))) {
                    return $binding;
                }
            }
        }
    ```  
    
    检测是否是匿名函数或是具体类==抽像类
    ```php  
    protected function isBuildable($concrete, $abstract)
        {
            return $concrete === $abstract || $concrete instanceof Closure;
        }
    ```
    
    运行匿名函数或实例化类【反射】  
    ```php  
    public function build($concrete)
        {
            //是匿名函数，直接运行
            /**
            匿名函数的样子
            return function ($container, $parameters = []) use ($abstract, $concrete) {
                        if ($abstract == $concrete) {
                        //此时Illuminate\Contracts\Http\Kernel::class
                        //取得的匿名函数虽然运行，但是
                        //$abstract, $concrete不相等啊
                            return $container->build($concrete);
                        }
            
            //所以运行这个$concrete=App\Http\Kernel::class
            //再返回运行【递归处理】
            //再次递归时$concrete === $abstract他们就成立！！！
            //如果你没有仔细阅读【将会无法理解这车是怎么开的！！！！】
                        return $container->resolve(
                            $concrete, $parameters, $raiseEvents = false
                        );
                    };
            **/
            if ($concrete instanceof Closure) {
                return $concrete($this, $this->getLastParameterOverride());
            }
    
    //否则反射
    //反射的时候才会把具体的类放入堆栈数组
    //下面的反射没有必要说了吧，PHP基础
            $reflector = new ReflectionClass($concrete);
    
            if (! $reflector->isInstantiable()) {
                return $this->notInstantiable($concrete);
            }
    
            $this->buildStack[] = $concrete;
    
            $constructor = $reflector->getConstructor();
    
            if (is_null($constructor)) {
                array_pop($this->buildStack);
    
                return new $concrete;
            }
    
            $dependencies = $constructor->getParameters();
    
            $instances = $this->resolveDependencies(
                $dependencies
            );
    
            array_pop($this->buildStack);
    
            return $reflector->newInstanceArgs($instances);
        }
    ```
    
   检测是否共享 
   ```php  
   public function isShared($abstract)
       {
           return isset($this->instances[$abstract]) ||
                  (isset($this->bindings[$abstract]['shared']) &&
                  $this->bindings[$abstract]['shared'] === true);
       }
   ```
    到此Application->make()方法的大概流程就是     
    Container->make()->resolve()->build()     
    
    1、resolve方法的功能 从Application->aliases里取出类的别名【如果有】     
    再从Application->abstructAliases里取【根据别名取出类名】    
    2、【类】从Application->bindings[]里取，如果有返回匿名函数，否则返回自己     
    3、判断是否是匿名函数||或是等于自己【要看源码，认真看就想看片一样，不然你真不知道我在说什么】     
    4、运行匿名函数||反射类并实例化返回【运行匿名时可能会构成递归】    
    5、已经处理过的类保存在Application->resolve【】数组里       
    
    至此我们分析了Application->make()方法是怎么开车的了  
    如果还是没有看懂，麻烦你边开车边看，ok?     
    
    至此搞定App\Http\Kernel是怎么完成的了  
    
    
