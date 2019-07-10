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
    
    
    
    
   
    
    